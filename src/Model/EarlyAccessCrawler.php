<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model;

use AugmentedSteam\Server\Database\TEarlyAccess;
use AugmentedSteam\Server\Loader\Item;
use AugmentedSteam\Server\Loader\Loader;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Loader\Proxy\ProxyInterface;
use AugmentedSteam\Server\Model\DataObjects\DEarlyAccess;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlDeleteQuery;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class EarlyAccessCrawler extends Crawler
{
    private const MaxAttempts = 3;
    private const PageSize = 100;

    private DbDriver $db;
    private ProxyInterface $proxy;

    private TEarlyAccess $e;
    private SqlInsertQuery $insert;
    private int $timestamp;

    private int $requestCounter = 0;

    public function __construct(DbDriver $db, Loader $loader, LoggerInterface $logger, ProxyFactoryInterface $proxyFactory) {
        parent::__construct($loader, $logger);

        $this->db = $db;
        $this->proxy = $proxyFactory->createProxy();

        $e = new TEarlyAccess();
        $this->e = $e;
        $this->insert = (new SqlInsertQuery($this->db, $e))
            ->columns($e->appid, $e->timestamp)
            ->onDuplicateKeyUpdate($e->timestamp);

        $this->timestamp = time();
    }

    private function makeRequest(int $start, int $pageSize): void {
        $url = "https://store.steampowered.com/search/results/?query"
            ."&start={$start}"
            ."&count={$pageSize}"
            ."&dynamic_data="
            ."&sort_by=Name_ASC"
            ."&snr=1_7_7_230_7"
            ."&genre=Early%20Access"
            ."&infinite=1"
            ."&cc=us";

        $item = (new Item($url))
            ->setData(["start" => $start])
            ->setCurlOptions($this->proxy->getCurlOptions());

        $this->enqueueRequest($item);
        $this->requestCounter++;
    }

    protected function successHandler(Item $request, ResponseInterface $response, string $effectiveUri): void {
        if (!$this->mayProcess($request, $response, self::MaxAttempts)) {
            return;
        }

        $data = $response->getBody()->getContents();
        $json = json_decode($data, true);
        if (json_last_error() !== 0 || !isset($json['success']) || $json['success'] != 1) {
            return;
        }

        if (preg_match_all("#/app/(\d+)#", $json['results_html'], $m)) {
            $appids = $m[1];
            foreach ($appids as $appid) {
                $this->insert->stack(
                    (new DEarlyAccess())
                        ->setAppid((int)$appid)
                        ->setTimestamp($this->timestamp)
                );
            }

            $requestData = $request->getData();
            if ($requestData['start'] == 0) {
                $size = count($appids);
                for ($s = $size; $s < (int)$json['total_count']; $s += $size) {
                    $this->makeRequest($s, $size);
                }
            }

            $this->insert->persist();
            --$this->requestCounter;
        }
    }

    private function cleanup(): void {
        $e = $this->e;
        (new SqlDeleteQuery($this->db,
            "DELETE FROM $e
            WHERE $e->timestamp < :timestamp"
        ))->delete([
            ":timestamp" => $this->timestamp
        ]);
    }

    public function update() {
        $this->logger->info("Update start");

        $this->makeRequest(0, self::PageSize);

        $this->runLoader();
        $this->insert->persist();

        if ($this->requestCounter == 0) {
            $this->cleanup();
        }

        $this->logger->info("Update done, unresolved: {$this->requestCounter}");
    }
}
