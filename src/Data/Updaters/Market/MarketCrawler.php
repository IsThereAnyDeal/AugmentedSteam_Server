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
    private const int BatchCount = 150;
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

    private function getAppid(): int {
        $i = new TMarketIndex();

        // @phpstan-ignore-next-line
        return $this->db->select(<<<SQL
            SELECT $i->appid
            FROM $i
            WHERE $i->last_update <= :timestamp
            ORDER BY $i->last_update ASC,
                     $i->request_counter DESC
            LIMIT 1
            SQL
        )->params([
            ":timestamp" => time() - self::UpdateFrequency
        ])->fetchInt();
    }

    private function makeRequest(int $appid, int $start=0): void {
        $params = [
            "query" => "",
            "start" => $start,
            "count" => 100,
            "search_description" => 0,
            "sort_column" => "popular",
            "sort_dir" => "desc",
            "appid" => $appid,
            "norender" => 1,
        ];

        $url = "https://steamcommunity.com/market/search/render/?".http_build_query($params);
        $item = (new Item($url))
            ->setData(["appid" => $appid])
            ->setHeaders([
                "User-Agent" => "Mozilla/5.0 (Windows NT 10.4; WOW64) AppleWebKit/536.14 (KHTML, like Gecko) Chrome/52.0.2935.205 Safari/601.3 Edge/13.46571",
            ])
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

        $appid = intval($request->getData()['appid']); // @phpstan-ignore-line
        if ($json['start'] === 0 && $json['start'] < $json['total_count']) {
            $pageSize = $json['pagesize'];

            if ($pageSize > 0) {
                for ($start = $pageSize; $start <= $json['total_count']; $start += $pageSize) {
                    $this->makeRequest($appid, $start);
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

        $this->logger->info("", ["appid" => $appid, "start" => $json['start']]);
    }

    private function updateIndex(int $appid): void {
        $i = new TMarketIndex();
        $this->db->updateObj($i)
            ->columns($i->last_update, $i->request_counter)
            ->where($i->appid)
            ->update(
                (new DMarketIndex())
                    ->setLastUpdate($this->timestamp)
                    ->setRequestCounter(0)
                    ->setAppid($appid)
            );
    }

    private function cleanup(int $appid): void {
        if (empty($appid)) { return; }

        $d = new TMarketData();
        $this->db->delete(<<<SQL
            DELETE FROM $d
            WHERE $d->appid=:appid
              AND $d->timestamp < :timestamp
            SQL
        )->delete([
            ":appid" => $appid,
            ":timestamp" => $this->timestamp
        ]);
    }

    public function update(): void {
        $this->logger->info("Update start");

        for ($b = 0; $b < self::BatchCount; $b++) {
            $appid = $this->getAppid();
            if (empty($appid)) { break; }

            $this->makeRequest($appid, 0);

            $this->runLoader();
            $this->updateIndex($appid);

            if ($this->requestCounter === 0) {
                $this->cleanup($appid);
                $this->logger->info("Batch done");
            } else {
                $this->logger->notice("Batch failed to finish ({$this->requestCounter} requests left)");
            }
            $this->requestCounter = 0;
        }

        $this->logger->info("Update done");
    }
}
