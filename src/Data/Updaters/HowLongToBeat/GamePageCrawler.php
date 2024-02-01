<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Updaters\HowLongToBeat;

use AugmentedSteam\Server\Database\DHLTB;
use AugmentedSteam\Server\Database\THLTB;
use AugmentedSteam\Server\Lib\Loader\Crawler;
use AugmentedSteam\Server\Lib\Loader\Item;
use AugmentedSteam\Server\Lib\Loader\Loader;
use AugmentedSteam\Server\Lib\Loader\Proxy\ProxyInterface;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\Create\SqlInsertQuery;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class GamePageCrawler extends Crawler
{
    private const int BatchSize = 250;
    private const int RecheckTimestamp = 14*86400;
    private const int MaxAttempts = 3;

    private readonly DbDriver $db;
    private readonly ProxyInterface $proxy;

    private int $requestCounter = 0;

    private GamePageParser $parser;
    private SqlInsertQuery $insert;

    public function __construct(
        DbDriver $db,
        Loader $loader,
        LoggerInterface $logger,
        ProxyInterface $proxy
    ) {
        parent::__construct($loader, $logger);
        $this->db = $db;
        $this->proxy = $proxy;
        $this->parser = new GamePageParser();

        $h = new THLTB();
        $this->insert = $this->db->insert($h)
            ->columns($h->id, $h->appid, $h->main, $h->extra, $h->complete, $h->checked_timestamp)
            ->onDuplicateKeyUpdate($h->appid, $h->main, $h->extra, $h->complete, $h->checked_timestamp);
    }

    private function makeRequest(int $id): void {
        $item = (new Item("https://howlongtobeat.com/game/$id"))
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
        $this->insert->stack(
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

    public function update(): void {
        $this->logger->info("Update start");

        $h = new THLTB();
        $ids = $this->db->select(<<<SQL
            SELECT $h->id
            FROM $h
            WHERE $h->appid IS NULL
              AND ($h->checked_timestamp IS NULL OR $h->checked_timestamp < :timestamp)
            ORDER BY $h->checked_timestamp, $h->found_timestamp DESC
            LIMIT :limit
            SQL
        )->params([
            ":timestamp" => time() - self::RecheckTimestamp,
            ":limit" => self::BatchSize
        ])->fetchValueArray();

        foreach($ids as $id) {
            $this->makeRequest((int)$id);
        }

        $this->runLoader();
        $this->insert->persist();

        $this->logger->info("Update done, unresolved: {$this->requestCounter}");
    }
}
