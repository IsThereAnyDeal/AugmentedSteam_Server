<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Market;

use AugmentedSteam\Server\Loader\Loader;
use AugmentedSteam\Server\Loader\Item;
use AugmentedSteam\Server\Model\DataObjects\DMarketData;
use AugmentedSteam\Server\Model\DataObjects\DMarketIndex;
use AugmentedSteam\Server\Model\Tables\TMarketData;
use AugmentedSteam\Server\Model\Tables\TMarketIndex;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlDeleteQuery;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use IsThereAnyDeal\Database\Sql\SqlUpdateObjectQuery;
use League\Uri\QueryString;
use League\Uri\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use SplQueue;
use Throwable;

class MarketUpdater
{
    private const BatchCount = 5;
    private const RequestBatchSize = 30;
    private const UpdateFrequency = 60*60;
    private const MaxAttempts = 3;

    private DbDriver $db;
    private Loader $loader;
    private LoggerInterface $logger;

    private SplQueue $requestQueue;
    private SqlInsertQuery $insertQuery;

    private int $requestCounter = 0;
    private int $timestamp;

    public function __construct(DbDriver $db, Loader $loader, LoggerInterface $logger) {
        $this->db = $db;
        $this->loader = $loader;
        $this->logger = $logger;

        $this->requestQueue = new SplQueue();
        $this->timestamp = time();

        $d = new TMarketData();
        $this->insertQuery = (new SqlInsertQuery($this->db, $d))
            ->columns(
                $d->hash_name, $d->appid,
                $d->appname,
                $d->name, $d->sell_listings, $d->sell_price_usd, $d->img,
                $d->type, $d->rarity, $d->timestamp
            )
            ->onDuplicateKeyUpdate(
                $d->appname,
                $d->name, $d->sell_listings, $d->sell_price_usd, $d->img,
                $d->type, $d->rarity, $d->timestamp
            )
            ->stackSize(0);
    }

    private function getAppidBatch(): array {
        $i = new TMarketIndex();

        return (new SqlSelectQuery($this->db,
            "SELECT $i->appid
            FROM $i
            WHERE $i->last_update <= :timestamp
            ORDER BY $i->last_update DESC
            LIMIT :limit"
        ))->params([
            ":timestamp" => time() - self::UpdateFrequency,
            ":limit" => self::RequestBatchSize
        ])->fetchValueArray();
    }

    private function makeRequest(array $appids, int $start=0): void {
        $params = [
            ["query", ""],
            ["start", $start],
            ["count", 100],
            ["search_description", 0],
            ["sort_column", "popular"],
            ["sort_dir", "desc"],
            ["norender", 1],
        ];
        foreach($appids as $appid) {
            $params[] = ["category_753_Game[]", "tag_app_{$appid}"];
        }

        $uri = Uri::createFromString("https://steamcommunity.com/market/search/render/")
            ->withQuery(QueryString::build($params));

        $item = (new Item((string)$uri))
            ->setData(["appids" => $appids]);

        $this->enqueueRequest($item);
        $this->requestCounter++;
    }

    private function enqueueRequest(Item $item): void {
        $request = $this->loader->createRequest(
            $item,
            fn(Item $item, ResponseInterface $response, string $effectiveUri) => $this->successHandler($item, $response, $effectiveUri),
            fn(Item $item, Throwable $e) => $this->errorHandler($item, $e)
        );
        $this->requestQueue->enqueue($request);
    }

    private function successHandler(Item $request, ResponseInterface $response, string $effectiveUri) {
        $status = $response->getStatusCode();
        if ($status !== 200) {
            if ($status === 429) {
                $this->logger->info("Throttling");
                sleep(60);
            }
            if ($request->getAttempt() <= self::MaxAttempts) {
                // replay request
                $request->incrementAttempt();
                $this->enqueueRequest($request);
                $this->logger->info("Retrying");
            } else {
                $this->logger->error($request->getUrl());
            }
            return;
        }

        $data = $response->getBody()->getContents();
        $json = json_decode($data, true);

        $appids = $request->getData()['appids'];
        if ($json['start'] === 0 && $json['start'] < $json['total_count']) {
            $pageSize = $json['pagesize'];

            if ($pageSize > 0) {
                for ($start = $pageSize; $start <= $json['total_count']; $start += $pageSize) {
                    $this->makeRequest($appids, $start);
                }
            }
        }
        foreach($json['results'] as $item) {
            $asset = $item['asset_description'];

            if (preg_match(
                "#^(.+?)(?:\s+(Uncommon|Foil|Rare|))?\s+(Profile Background|Emoticon|Booster Pack|Trading Card|Sale Item)$#",
                $asset['type'] === "Booster Pack" ? $asset['name'] : $asset['type'],
                $m
            )) {
                $appName = $m[1];

                switch($m[2]) {
                    case "Uncommon":
                    case "Foil":
                    case "Rare":
                        $rarity = strtolower($m[2]);
                        break;
                    default:
                        $rarity = "normal";
                        break;
                }

                switch($m[3]) {
                    case "Profile Background":
                        $type = "background";
                        break;
                    case "Emoticon":
                        $type = "emoticon";
                        break;
                    case "Booster Pack":
                        $type = "booster";
                        break;
                    case "Trading Card":
                        $type = "card";
                        break;
                    case "Sale Item":
                        $type = "item";
                        break;
                    default:
                        $type = "unknown";
                        break;
                }
            } else {
                $appName = $asset['type'];
                $rarity = "normal";
                $type = "unknown";
                $this->logger->notice($appName);
            }

            list($appid) = explode("-", $item['hash_name'], 2);
            $this->insertQuery->stack(
                (new DMarketData())
                    ->setHashName($item['hash_name'])
                    ->setAppid((int)$appid)
                    ->setAppName($appName)
                    ->setName($item['name'])
                    ->setSellListings($item['sell_listings'])
                    ->setSellPriceUsd($item['sell_price'])
                    ->setImg($asset['icon_url'])
                    ->setType($type)
                    ->setRarity($rarity)
                    ->setTimestamp(time())
            );
        }

        $this->insertQuery->persist();
        --$this->requestCounter;

        $this->logger->info("", ["appids" => $appids, "start" => $json['start']]);
    }

    private function errorHandler(Item $item, Throwable $e) {
        $this->logger->error($e->getMessage().": ".$item->getUrl());
    }

    private function requestGenerator(): \Generator {
        while(true) {
            if ($this->requestQueue->isEmpty()) { break; }
            yield $this->requestQueue->dequeue();
        }
    }

    private function updateIndex(array $appids): void {
        $i = new TMarketIndex();
        (new SqlUpdateObjectQuery($this->db, $i))
            ->columns($i->last_update, $i->request_counter)
            ->whereSql("$i->appid IN :appids", [":appids" => $appids])
            ->update(
                (new DMarketIndex())
                    ->setLastUpdate($this->timestamp)
                    ->setRequestCounter(0)
            );
    }

    private function cleanup(array $appids): void {
        $d = new TMarketData();
        (new SqlDeleteQuery($this->db,
            "DELETE FROM $d
            WHERE $d->appid IN :appids AND $d->timestamp < :timestamp"
        ))->delete([
            ":appids" => $appids,
            ":timestamp" => $this->timestamp
        ]);
    }

    public function update() {
        $this->logger->info("Update start");

        for ($b = 0; $b < self::BatchCount; $b++) {
            $appids = $this->getAppidBatch();
            $this->makeRequest($appids, 0);

            while (true) {
                if ($this->requestQueue->isEmpty()) { break; }
                $this->loader->run($this->requestGenerator());
            }

            $this->updateIndex($appids);

            if ($this->requestCounter === 0) {
                $this->cleanup($appids);
                $this->logger->info("Batch done");
            } else {
                $this->logger->notice("Batch failed to finish ({$this->requestCounter} requests left)");
            }
            $this->requestCounter = 0;
        }

        $this->logger->info("Update done");
    }
}
