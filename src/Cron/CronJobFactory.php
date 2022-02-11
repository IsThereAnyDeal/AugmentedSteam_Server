<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Cron;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\ExfglsConfig;
use AugmentedSteam\Server\Config\KeysConfig;
use AugmentedSteam\Server\Loader\Loader;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\EarlyAccessCrawler;
use AugmentedSteam\Server\Model\HowLongToBeat\GamePageCrawler;
use AugmentedSteam\Server\Model\HowLongToBeat\SearchResultsCrawler;
use AugmentedSteam\Server\Model\Market\MarketCrawler;
use AugmentedSteam\Server\Model\Money\RatesManager;
use AugmentedSteam\Server\Model\StorePage\ExfglsManager;
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
                $logger = $this->loggerFactory->createLogger("market");

                $loader = new Loader($logger, $this->guzzle);
                $updater = new MarketCrawler($this->db, $loader, $logger, $this->proxyFactory);
                $updater->update();
            });
    }

    public function createHLTBSearchResultsAllJob(): CronJob {
        return (new CronJob())
            ->lock("hltb.all", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->createLogger("hltb");

                $loader = new Loader($logger, $this->guzzle);
                $updater = new SearchResultsCrawler($this->db, $loader, $logger, $this->proxyFactory);
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

    public function createEarlyAccessJob(): CronJob {
        return (new CronJob())
            ->lock("earlyaccess", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->createLogger("earlyaccess");

                $loader = new Loader($logger, $this->guzzle);
                $updater = new EarlyAccessCrawler($this->db, $loader, $logger, $this->proxyFactory);
                $updater->update();
            });
    }

    public function createRatesJob(): CronJob {
        return (new CronJob())
            ->lock("rates", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->createLogger("rates");

                $loader = new SimpleLoader($this->guzzle, $logger);
                $updater = new RatesManager($this->db, $loader, $this->endpointsConfig, $this->keysConfig, $logger);
                $updater->updateRates();
            });
    }

    public function createExfglsJob(): CronJob {
        return (new CronJob())
            ->lock("exfgls", 5)
            ->callable(function(){
                $updater = new ExfglsManager($this->db, $this->loggerFactory, $this->exfglsConfig);
                $updater->update();
            });
    }
}
