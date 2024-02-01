<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Updaters\HowLongToBeat;

use AugmentedSteam\Server\Database\DHLTB;
use AugmentedSteam\Server\Database\THLTB;
use AugmentedSteam\Server\Lib\Loader\Crawler;
use AugmentedSteam\Server\Lib\Loader\Item;
use AugmentedSteam\Server\Lib\Loader\Loader;
use AugmentedSteam\Server\Lib\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Lib\Loader\Proxy\ProxyInterface;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\Create\SqlInsertQuery;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class SearchResultsCrawler extends Crawler
{
    private const int MaxAttempts = 3;

    private DbDriver $db;
    private ProxyInterface $proxy;

    private int $requestCounter = 0;

    private SqlInsertQuery $insert;
    private string $queryString = "";

    public function __construct(DbDriver $db, Loader $loader, LoggerInterface $logger, ProxyFactoryInterface $proxyFactory) {
        parent::__construct($loader, $logger);
        $this->db = $db;
        $this->proxy = $proxyFactory->createProxy();

        $h = new THLTB();
        $this->insert = $this->db->insert($h)
            ->columns($h->id, $h->main, $h->extra, $h->complete, $h->found_timestamp)
            ->onDuplicateKeyUpdate($h->main, $h->extra, $h->complete, $h->found_timestamp);
    }

    public function setQueryString(string $queryString): void {
        $this->queryString = $queryString;
    }

    private function makeRequest(int $page=1): void {

        $item = (new Item("https://howlongtobeat.com/api/search"))
            ->setMethod("POST")
            ->setBody(json_encode([
                "searchType" => "games",
                "searchTerms" => explode(" ", $this->queryString),
                "searchPage" => $page,
                "size" => 20,
                "searchOptions"=> [
                    "games" => [
                        "userId" => 0,
                        "platform" => "PC",
                        "sortCategory" => "popular",
                        "rangeCategory" => "main",
                        "rangeTime" => [
                            "min" => 0,
                            "max" => 0
                        ],
                        "gameplay" => [
                            "perspective" => "",
                            "flow" => "",
                            "genre" => ""
                        ],
                        "modifier" => ""
                    ],
                    "users" => [
                        "sortCategory" => "postcount"
                    ],
                    "filter" => "",
                    "sort" => 0,
                    "randomizer" => 0
                ]
            ]))
            ->setHeaders([
                "Content-Type" => "application/json",
                "User-Agent" => "AugmentedSteam/1.0 (+bots@isthereanydeal.com)",
                "Referer" => "https://howlongtobeat.com/",
            ])
            ->setData(["page" => $page])
            ->setCurlOptions($this->proxy->getCurlOptions());

        $this->enqueueRequest($item);
        $this->requestCounter++;
    }

    protected function successHandler(Item $request, ResponseInterface $response, string $effectiveUri): void {
        if (!$this->mayProcess($request, $response, self::MaxAttempts)) {
            return;
        }

        $json = json_decode($response->getBody()->getContents(), true);

        $requestData = $request->getData();
        $page = $requestData['page'];
        if ($page == 1) {
            $pages = $json['pageTotal'];
            for ($p=2; $p <= $pages; $p++) {
                $this->makeRequest($p);
            }
        }

        foreach($json['data'] as $item) {
            $id = $item['game_id'];

            $this->insert
                ->stack(
                    (new DHLTB())
                        ->setId((int)$id)
                        ->setMain(is_null($item['comp_main']) ? null : (int)floor($item['comp_main']/60))
                        ->setExtra(is_null($item['comp_plus']) ? null : (int)floor($item['comp_plus']/60))
                        ->setComplete(is_null($item['comp_100']) ? null : (int)floor($item['comp_100']/60))
                        ->setFoundTimestamp(time())
                );
        }

        $this->insert->persist();
        --$this->requestCounter;

        $this->logger->info("Parsed page $page");
    }

    public function update() {
        $this->logger->info("Update start [$this->queryString]");

        $this->makeRequest(1);
        $this->runLoader();

        $this->logger->info("Update done [$this->queryString], unresolved: {$this->requestCounter}");
    }
}
