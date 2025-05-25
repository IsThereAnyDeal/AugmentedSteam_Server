<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Updaters\Market;

use AugmentedSteam\Server\Data\Managers\Market\ERarity;
use AugmentedSteam\Server\Data\Managers\Market\EType;
use AugmentedSteam\Server\Database\DMarketData;
use AugmentedSteam\Server\Database\DMarketIndex;
use AugmentedSteam\Server\Database\TMarketData;
use AugmentedSteam\Server\Database\TMarketIndex;
use AugmentedSteam\Server\Lib\Loader\Crawler;
use AugmentedSteam\Server\Lib\Loader\Item;
use AugmentedSteam\Server\Lib\Loader\Loader;
use AugmentedSteam\Server\Lib\Loader\Proxy\ProxyInterface;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\Create\SqlInsertQuery;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class MarketCrawler extends Crawler
{
    private const int BatchCount = 3;
    private const int RequestBatchSize = 50;
    private const int UpdateFrequency = 60*60;
    private const int MaxAttempts = 3;

    private readonly DbDriver $db;
    private readonly ProxyInterface $proxy;

    private readonly SqlInsertQuery $insertQuery;

    private int $requestCounter = 0;
    private int $timestamp;

    public function __construct(
        DbDriver $db,
        Loader $loader,
        LoggerInterface $logger,
        ProxyInterface $proxy
    ) {
        parent::__construct($loader, $logger);
        $this->db = $db;
        $this->proxy = $proxy;

        $this->timestamp = time();

        $d = new TMarketData();
        $this->insertQuery = $this->db->insert($d)
            ->columns(
                $d->hash_name, $d->appid,
                $d->appname,
                $d->name, $d->sell_listings, $d->sell_price_usd, $d->img, $d->url,
                $d->type, $d->rarity, $d->timestamp
            )
            ->onDuplicateKeyUpdate(
                $d->appname,
                $d->name, $d->sell_listings, $d->sell_price_usd, $d->img, $d->url,
                $d->type, $d->rarity, $d->timestamp
            );
    }

    /**
     * @return list<int>
     */
    private function getAppidBatch(): array {
        $i = new TMarketIndex();

        // @phpstan-ignore-next-line
        return $this->db->select(<<<SQL
            SELECT $i->appid
            FROM $i
            WHERE $i->last_update <= :timestamp
            ORDER BY $i->last_update ASC,
                     $i->request_counter DESC
            LIMIT :limit
            SQL
        )->params([
            ":timestamp" => time() - self::UpdateFrequency,
            ":limit" => self::RequestBatchSize
        ])->fetchValueArray();
    }

    /**
     * @param list<int> $appids
     */
    private function makeRequest(array $appids, int $start=0): void {
        $params = [
            "query" => "",
            "start" => $start,
            "count" => 100,
            "search_description" => 0,
            "sort_column" => "popular",
            "sort_dir" => "desc",
            "norender" => 1,
        ];
        foreach($appids as $appid) {
            $params['category_753_Game'][] = "tag_app_{$appid}";
        }

        $url = "https://steamcommunity.com/market/search/render/?".http_build_query($params);
        $item = (new Item($url))
            ->setData(["appids" => $appids])
            ->setCurlOptions($this->proxy->getCurlOptions());

        $this->enqueueRequest($item);
        $this->requestCounter++;
    }

    protected function successHandler(Item $request, ResponseInterface $response, string $effectiveUri): void {
        if (!$this->mayProcess($request, $response, self::MaxAttempts)) {
            return;
        }

        $data = $response->getBody()->getContents();

        /**
         * @var array{
         *     success: bool,
         *     start: int,
         *     pagesize: int,
         *     total_count: int,
         *     results: list<array{
         *         name: string,
         *         hash_name: string,
         *         sell_listings: int,
         *         sell_price: int,
         *         app_name: string,
         *         asset_description: array{
         *             appid: int,
         *             type: string,
         *             name: string,
         *             icon_url: string
         *         }
         *     }>
         * } $json
         */
        $json = json_decode($data, true);

        /** @var list<int> $appids */
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

            $rarity = ERarity::Normal;
            $type = EType::Unknown;

            if ($item['app_name'] == "Steam") {
                if (preg_match(
                    "#^(.+?)(?:\s+(Uncommon|Foil|Rare|))?\s+(Profile Background|Emoticon|Booster Pack|Trading Card|Sale Item)$#",
                    $asset['type'] === "Booster Pack" ? $asset['name'] : $asset['type'],
                    $m
                )) {
                    $appName = $m[1];

                    $rarity = match($m[2]) {
                        "Uncommon" => ERarity::Uncommon,
                        "Foil" => ERarity::Foil,
                        "Rare" => ERarity::Rare,
                        default => ERarity::Normal
                    };

                    $type = match($m[3]) {
                        "Profile Background" => EType::Background,
                        "Emoticon" => EType::Emoticon,
                        "Booster Pack" => EType::Booster,
                        "Trading Card" => EType::Card,
                        "Sale Item" => EType::Item
                    };
                } else {
                    $appName = $asset['type'];
                    $this->logger->notice($appName);
                }
            } else {
                $appName = $item['app_name'];
                $type = match($asset['type']) {
                    "Profile Background" => EType::Background,
                    "Emoticon" => EType::Emoticon,
                    "Booster Pack" => EType::Booster,
                    "Trading Card" => EType::Card,
                    "Sale Item" => EType::Item,
                    default => EType::Unknown
                };
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
                    ->setUrl($asset['appid']."/".rawurlencode($item['hash_name']))
                    ->setType($type)
                    ->setRarity($rarity)
                    ->setTimestamp(time())
            );
        }

        $this->insertQuery->persist();
        --$this->requestCounter;

        $this->logger->info("", ["appids" => $appids, "start" => $json['start']]);
    }

    /**
     * @param list<int> $appids
     */
    private function updateIndex(array $appids): void {
        $i = new TMarketIndex();
        $this->db->updateObj($i)
            ->columns($i->last_update, $i->request_counter)
            ->whereSql("$i->appid IN :appids", [":appids" => $appids])
            ->update(
                (new DMarketIndex())
                    ->setLastUpdate($this->timestamp)
                    ->setRequestCounter(0)
            );
    }

    /**
     * @param list<int> $appids
     */
    private function cleanup(array $appids): void {
        if (count($appids) == 0) { return; }

        $d = new TMarketData();
        $this->db->delete(<<<SQL
            DELETE FROM $d
            WHERE $d->appid IN :appids
              AND $d->timestamp < :timestamp
            SQL
        )->delete([
            ":appids" => $appids,
            ":timestamp" => $this->timestamp
        ]);
    }

    public function update(): void {
        $this->logger->info("Update start");

        for ($b = 0; $b < self::BatchCount; $b++) {
            $appids = $this->getAppidBatch();
            if (count($appids) == 0) { break; }

            $this->makeRequest($appids, 0);

            $this->runLoader();
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
