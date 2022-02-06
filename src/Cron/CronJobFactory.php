<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Cron;

use AugmentedSteam\Server\Loader\Loader;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\HowLongToBeat\GamePageCrawler;
use AugmentedSteam\Server\Model\HowLongToBeat\SearchResultsCrawler;
use AugmentedSteam\Server\Model\Market\MarketCrawler;
use GuzzleHttp\Client;
use IsThereAnyDeal\Database\DbDriver;

class CronJobFactory
{
    private LoggerFactoryInterface $loggerFactory;
    private ProxyFactoryInterface $proxyFactory;
    private DbDriver $db;
    private Client $guzzle;

    public function __construct(
        LoggerFactoryInterface $loggerFactory,
        ProxyFactoryInterface $proxyFactory,
        DbDriver $db,
        Client $guzzle
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->proxyFactory = $proxyFactory;
        $this->db = $db;
        $this->guzzle = $guzzle;
    }

    public function createMarketJob(): CronJob {
        return (new CronJob())
            ->lock("market", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->createLogger("market");

                $loader = new Loader($logger, $this->guzzle);
                $updater = new MarketCrawler($this->db, $loader, $logger, $this->proxyFactory);
                $updater->update();
            });
    }

    public function createHLTBSearchResultsRecentJob(): CronJob {
        return (new CronJob())
            ->lock("hltb.recent", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->createLogger("hltb");

                $loader = new Loader($logger, $this->guzzle);
                $updater = new SearchResultsCrawler($this->db, $loader, $logger, $this->proxyFactory);
                $updater->setQueryString("recently added");
                $updater->update();
            });
    }

    public function createHLTBGamesJob(): CronJob {
        return (new CronJob())
            ->lock("hltb.games", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->createLogger("hltb");

                $loader = new Loader($logger, $this->guzzle);
                $updater = new GamePageCrawler($this->db, $loader, $logger, $this->proxyFactory);
                $updater->update();
            });
    }
}
