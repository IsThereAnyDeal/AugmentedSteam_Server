<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Cron;

use AugmentedSteam\Server\Loader\Loader;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\Market\MarketUpdater;
use GuzzleHttp\Client;
use IsThereAnyDeal\Database\DbDriver;

class CronJobFactory
{
    private LoggerFactoryInterface $loggerFactory;
    private DbDriver $db;
    private Client $guzzle;

    public function __construct(LoggerFactoryInterface $loggerFactory, DbDriver $db, Client $guzzle) {
        $this->loggerFactory = $loggerFactory;
        $this->db = $db;
        $this->guzzle = $guzzle;
    }

    public function createMarketJob(): CronJob {
        return (new CronJob())
            ->lock("market", 5)
            ->callable(function(){
                $logger = $this->loggerFactory->createLogger("market");

                $loader = new Loader($logger, $this->guzzle);
                $updater = new MarketUpdater($this->db, $loader, $logger);
                $updater->update();
            });
    }
}
