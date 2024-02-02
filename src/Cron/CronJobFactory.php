<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Cron;

use AugmentedSteam\Server\Data\Interfaces\RatesProviderInterface;
use AugmentedSteam\Server\Data\Updaters\Exfgls\ExfglsConfig;
use AugmentedSteam\Server\Data\Updaters\Exfgls\ExfglsUpdater;
use AugmentedSteam\Server\Data\Updaters\HowLongToBeat\GamePageCrawler;
use AugmentedSteam\Server\Data\Updaters\HowLongToBeat\SearchResultsCrawler;
use AugmentedSteam\Server\Data\Updaters\Market\MarketCrawler;
use AugmentedSteam\Server\Data\Updaters\Rates\RatesUpdater;
use AugmentedSteam\Server\Database\TCache;
use AugmentedSteam\Server\Environment\Container;
use AugmentedSteam\Server\Lib\Loader\Loader;
use AugmentedSteam\Server\Lib\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Lib\Logging\LoggerFactoryInterface;
use GuzzleHttp\Client;
use InvalidArgumentException;
use IsThereAnyDeal\Database\DbDriver;

class CronJobFactory
{
    private readonly LoggerFactoryInterface $loggerFactory;
    private readonly DbDriver $db;

    public function __construct(
        private readonly Container $container
    ) {
        $this->loggerFactory = $this->container->get(LoggerFactoryInterface::class);
        $this->db = $this->container->get(DbDriver::class);
    }

    private function createRatesJob(): CronJob {
        return (new CronJob())
            ->lock("rates", 5)
            ->callable(function(){
                $container = Container::getInstance();
                $logger = $this->loggerFactory->logger("rates");

                $provider = $container->get(RatesProviderInterface::class);
                $updater = new RatesUpdater($this->db, $provider, $logger);
                $updater->update();
            });
    }

    private function createExfglsJob(): CronJob {
        return (new CronJob())
            ->lock("exfgls", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->logger("exfgls");
                $exfglsConfig = $this->container->get(ExfglsConfig::class);

                $updater = new ExfglsUpdater($this->db, $logger, $exfglsConfig);
                $updater->update();
            });
    }

    private function createMarketJob(): CronJob {
        return (new CronJob())
            ->lock("market", 10)
            ->callable(function(){
                $logger = $this->loggerFactory->logger("market");
                $guzzle = $this->container->get(Client::class);
                $proxy = $this->container->get(ProxyFactoryInterface::class)
                    ->createProxy();

                $loader = new Loader($logger, $guzzle);
                $updater = new MarketCrawler($this->db, $loader, $logger, $proxy);
                $updater->update();
            });
    }

    private function createHLTBSearchResultsAllJob(): CronJob {
        return (new CronJob())
            ->lock("hltb.all", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->logger("hltb");
                $guzzle = $this->container->get(Client::class);
                $proxy = $this->container->get(ProxyFactoryInterface::class)
                    ->createProxy();

                $loader = new Loader($logger, $guzzle);
                $updater = new SearchResultsCrawler($this->db, $loader, $logger, $proxy);
                $updater->update();
            });
    }

    private function createHLTBSearchResultsRecentJob(): CronJob {
        return (new CronJob())
            ->lock("hltb.recent", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->logger("hltb");
                $guzzle = $this->container->get(Client::class);
                $proxy = $this->container->get(ProxyFactoryInterface::class)
                    ->createProxy();

                $loader = new Loader($logger, $guzzle);
                $updater = new SearchResultsCrawler($this->db, $loader, $logger, $proxy);
                $updater->setQueryString("recently added");
                $updater->update();
            });
    }

    private function createHLTBGamesJob(): CronJob {
        return (new CronJob())
            ->lock("hltb.games", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->logger("hltb");
                $guzzle = $this->container->get(Client::class);
                $proxy = $this->container->get(ProxyFactoryInterface::class)
                    ->createProxy();

                $loader = new Loader($logger, $guzzle);
                $updater = new GamePageCrawler($this->db, $loader, $logger, $proxy);
                $updater->update();
            });
    }

    private function createCacheMaintenanceJob(): CronJob {
        return (new CronJob())
            ->callable(function(){
                $db = $this->container->get(DbDriver::class);

                $c = new TCache();
                $db->delete(<<<SQL
                    DELETE FROM $c
                    WHERE $c->expiry < UNIX_TIMESTAMP()
                    SQL
                )->delete();
            });
    }

    public function getJob(string $job): CronJob {

        return match($job) {
            "rates" => $this->createRatesJob(),
            "exfgls" => $this->createExfglsJob(),
            "market" => $this->createMarketJob(),
            "hltb-all" => $this->createHLTBSearchResultsAllJob(),
            "hltb-recent" => $this->createHLTBSearchResultsRecentJob(),
            "hltb-games" => $this->createHLTBGamesJob(),
            "cache-maintenance" => $this->createCacheMaintenanceJob(),
            default => throw new InvalidArgumentException()
        };
    }
}
