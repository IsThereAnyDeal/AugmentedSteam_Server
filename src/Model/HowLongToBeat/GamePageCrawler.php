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
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class GamePageCrawler extends Crawler
{
    private const BatchSize = 250;
    private const RecheckTimestamp = 31*86400;
    private const MaxAttempts = 3;

    private DbDriver $db;
    private ProxyInterface $proxy;

    private int $requestCounter = 0;

    private GamePageParser $parser;
    private SqlInsertQuery $insert;

    public function __construct(DbDriver $db, Loader $loader, LoggerInterface $logger, ProxyFactoryInterface $proxyFactory) {
        parent::__construct($loader, $logger);
        $this->db = $db;
        $this->proxy = $proxyFactory->createProxy();
        $this->parser = new GamePageParser();

        $h = new THLTB();
        $this->insert = (new SqlInsertQuery($this->db, $h))
            ->columns($h->id, $h->appid, $h->main, $h->extra, $h->complete, $h->checked_timestamp)
            ->onDuplicateKeyUpdate($h->appid, $h->main, $h->extra, $h->complete, $h->checked_timestamp);
    }

    private function makeRequest(int $id): void {
        $item = (new Item("https://howlongtobeat.com/game.php?id=$id"))
            ->setData(["id" => $id])
            ->setHeaders(["User-Agent" => "AugmentedSteam/1.0 (+bots@isthereanydeal.com)"])
            ->setCurlOptions($this->proxy->getCurlOptions());

        $this->enqueueRequest($item);
        $this->requestCounter++;
    }

    protected function successHandler(Item $request, ResponseInterface $response, string $effectiveUri): void {
        if (!$this->mayProcess($request, $response, self::MaxAttempts)) {
            return;
        }

        $data = $this->parser->parse($response->getBody()->getContents());

        $id = $request->getData()['id'];
        $this->insert
            ->stack(
                (new DHLTB())
                    ->setId($id)
                    ->setAppid($data['appid'] ?? null)
                    ->setMain($data['times']['main'] ?? null)
                    ->setExtra($data['times']['extra'] ?? null)
                    ->setComplete($data['times']['complete'] ?? null)
                    ->setCheckedTimestamp(time())
            );

        --$this->requestCounter;

        $this->logger->info("$id");
    }

    public function update() {
        $this->logger->info("Update start");

        $h = new THLTB();
        $ids = (new SqlSelectQuery($this->db,
            "SELECT $h->id
            FROM $h
            WHERE $h->appid IS NULL
              AND ($h->checked_timestamp IS NULL OR $h->checked_timestamp >= :timestamp)
            ORDER BY $h->found_timestamp DESC
            LIMIT ".self::BatchSize
        ))->params([
            ":timestamp" => time() - self::RecheckTimestamp
        ])->fetchValueArray();

        foreach($ids as $id) {
            $this->makeRequest((int)$id);
        }

        $this->runLoader();
        $this->insert->persist();

        $this->logger->info("Update done, unresolved: {$this->requestCounter}");
    }
}
