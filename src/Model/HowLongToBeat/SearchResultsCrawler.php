<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\HowLongToBeat;

use AugmentedSteam\Server\Loader\Loader;
use AugmentedSteam\Server\Loader\Item;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Loader\Proxy\ProxyInterface;
use AugmentedSteam\Server\Model\Crawler;
use AugmentedSteam\Server\Model\DataObjects\DHLTB;
use AugmentedSteam\Server\Model\Tables\THLTB;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class SearchResultsCrawler extends Crawler
{
    private const MaxAttempts = 3;

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
        $this->insert = (new SqlInsertQuery($this->db, $h))
            ->columns($h->id, $h->main, $h->extra, $h->complete, $h->found_timestamp)
            ->onDuplicateKeyUpdate($h->main, $h->extra, $h->complete, $h->found_timestamp);
    }

    public function setQueryString(string $queryString) {
        $this->queryString = $queryString;
    }

    private function makeRequest(int $page=1): void {

        $item = (new Item("https://howlongtobeat.com/search_results?page=$page"))
            ->setMethod("POST")
            ->setFormData([
                "queryString" => $this->queryString,
                "t" => "games",
                "sorthead" => "popular",
                "sortd" => "0",
                "plat" => "PC",
                "length_type" => "main",
                "length_min" => "",
                "length_max" => "",
                "v" => "",
                "f" => "",
                "g" => "",
                "detail" => "",
                "randomize" => "0",
            ])
            ->setHeaders([
                "User-Agent" => "AugmentedSteam/1.0 (+bots@isthereanydeal.com)"
            ])
            ->setData(["page" => $page])
            ->setCurlOptions($this->proxy->getCurlOptions());

        $this->enqueueRequest($item);
        $this->requestCounter++;
    }

    private function getHours(string $hourString): ?int {
        $hourString = str_replace("½", ".5", $hourString);
        if (!preg_match("#\d+(\.5)?#", $hourString, $m)) {
            return null;
        }
        return (int)(((float)$m[0])*60);
    }

    private function parseTimeNodes(DOMNodeList $nodes): array {
        $result = [];
        for ($i=0; $i < count($nodes); $i += 2) {
            $result[trim($nodes[$i]->textContent)] = $this->getHours($nodes[$i+1]->textContent);
        }
        return $result;
    }

    protected function successHandler(Item $request, ResponseInterface $response, string $effectiveUri): void {
        if (!$this->mayProcess($request, $response, self::MaxAttempts)) {
            return;
        }

        $dom = new DOMDocument();
        $dom->loadHTML($response->getBody()->getContents(), LIBXML_NOERROR);

        $xpath = new DOMXPath($dom);

        $requestData = $request->getData();
        $page = $requestData['page'];
        if ($page == 1) {
            $pages = $xpath->query("//h2/span[contains(concat(' ', @class, ' '), 'search_list_page')][last()]")[0]->textContent;
            for ($p=2; $p <= $pages; $p++) {
                $this->makeRequest($p);
            }
        }

        $details = $xpath->query("//div[@class='search_list_details']");
        foreach($details as $detailNode) {
            $a = $xpath->query(".//a[1]", $detailNode)[0];
            $href = $xpath->query("./@href", $a)[0]->value;

            if (!preg_match("#id=(\d+)#", $href, $m)) {
                continue;
            }

            $id = $m[1];
            $timeNodes = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' search_list_tidbit ')]", $detailNode);
            $times = $this->parseTimeNodes($timeNodes);

            $this->insert
                ->stack(
                    (new DHLTB())
                        ->setId((int)$id)
                        ->setMain($times['Main Story'] ?? null)
                        ->setExtra($times['Main + Extra'] ?? null)
                        ->setComplete($times['Completionist'] ?? null)
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
