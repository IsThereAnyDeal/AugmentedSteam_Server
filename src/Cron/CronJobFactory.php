<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Cron;

use AugmentedSteam\Server\Data\Interfaces\RatesProviderInterface;
use AugmentedSteam\Server\Data\Updaters\Exfgls\ExfglsConfig;
use AugmentedSteam\Server\Data\Updaters\Exfgls\ExfglsUpdater;
use AugmentedSteam\Server\Data\Updaters\HowLongToBeat\GamePageCrawler;
use AugmentedSteam\Server\Data\Updaters\HowLongToBeat\SearchResultsCrawler;
use AugmentedSteam\Server\Data\Updaters\Rates\RatesUpdater;
use AugmentedSteam\Server\Endpoints\EndpointsConfig;
use AugmentedSteam\Server\Endpoints\KeysConfig;
use AugmentedSteam\Server\Environment\Container;
use AugmentedSteam\Server\Lib\Loader\Loader;
use AugmentedSteam\Server\Lib\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Lib\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\Market\MarketCrawler;
use GuzzleHttp\Client;
use IsThereAnyDeal\Database\DbDriver;

class CronJobFactory
{
    private LoggerFactoryInterface $loggerFactory;
    private ProxyFactoryInterface $proxyFactory;
    private DbDriver $db;
    private Client $guzzle;
    private EndpointsConfig $endpointsConfig;
    private KeysConfig $keysConfig;
    private ExfglsConfig $exfglsConfig;

    public function __construct(
        LoggerFactoryInterface $loggerFactory,
        ProxyFactoryInterface $proxyFactory,
        DbDriver $db,
        Client $guzzle,
        // TODO figure out a way how we don't have to have multiple config objects here
        EndpointsConfig $endpointsConfig,
        KeysConfig $keysConfig,
        ExfglsConfig $exfglsConfig
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->proxyFactory = $proxyFactory;
        $this->db = $db;
        $this->guzzle = $guzzle;
        $this->endpointsConfig = $endpointsConfig;
        $this->keysConfig = $keysConfig;
        $this->exfglsConfig = $exfglsConfig;
    }

    public function createMarketJob(): CronJob {
        return (new CronJob())
            ->lock("market", 10)
            ->callable(function(){
                $logger = $this->loggerFactory->create("market");

                $loader = new Loader($logger, $this->guzzle);
                $updater = new MarketCrawler($this->db, $loader, $logger, $this->proxyFactory);
                $updater->update();
            });
    }

    public function createHLTBSearchResultsAllJob(): CronJob {
        return (new CronJob())
            ->lock("hltb.all", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->create("hltb");

                $loader = new Loader($logger, $this->guzzle);
                $updater = new SearchResultsCrawler($this->db, $loader, $logger, $this->proxyFactory);
                $updater->update();
            });
    }

    public function createHLTBSearchResultsRecentJob(): CronJob {
        return (new CronJob())
            ->lock("hltb.recent", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->create("hltb");

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
                $logger = $this->loggerFactory->create("hltb");

                $loader = new Loader($logger, $this->guzzle);
                $updater = new GamePageCrawler($this->db, $loader, $logger, $this->proxyFactory);
                $updater->update();
            });
    }

    public function createRatesJob(): CronJob {
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

    public function createExfglsJob(): CronJob {
        return (new CronJob())
            ->lock("exfgls", 5)
            ->callable(function(){
                $updater = new ExfglsUpdater(
                    $this->db,
                    $this->loggerFactory->logger("exfgls"),
                    $this->exfglsConfig
                );
                $updater->update();
            });
    }
}
