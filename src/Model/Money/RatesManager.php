<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Money;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\KeysConfig;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Model\DataObjects\DCurrency;
use AugmentedSteam\Server\Model\Tables\TCurrency;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlDeleteQuery;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use Psr\Log\LoggerInterface;

class RatesManager {
    private const CacheLimit = 86400;

    private DbDriver $db;
    private SimpleLoader $loader;
    private EndpointsConfig $config;
    private KeysConfig $keysConfig;
    private LoggerInterface $logger;

    public function __construct(
        DbDriver $db,
        SimpleLoader $loader,
        EndpointsConfig $config,
        KeysConfig $keysConfig,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->loader = $loader;
        $this->config = $config;
        $this->keysConfig = $keysConfig;
        $this->logger = $logger;
    }

    public function updateRates(): void {
        $this->logger->info("Start");

        $host = $this->config->getIsThereAnyDealApiHost();
        $key = $this->keysConfig->getIsThereAnyDealApiKey();

        $result = $this->loader->get("$host/v01/augmentedsteam/rates/?key=$key");
        $json = json_decode($result->getBody()->getContents(), true);

        if (!isset($json['data'])) {
            throw new \Exception("No data");
        }

        $data = $json['data'];

        $c = new TCurrency();
        $insert = (new SqlInsertQuery($this->db, $c))
            ->columns($c->from, $c->to, $c->rate, $c->timestamp)
            ->onDuplicateKeyUpdate($c->rate, $c->timestamp)
            ->stackSize(100);

        foreach($data as $a) {
            $insert->stack((new DCurrency())
                ->setFrom($a['from'])
                ->setTo($a['to'])
                ->setRate((float)$a['rate'])
                ->setTimestamp(time())
            );
        }
        $insert->persist();

        (new SqlDeleteQuery($this->db,
            "DELETE FROM $c
            WHERE $c->timestamp < :timestamp"
        ))->delete([
            ":timestamp" => time() - self::CacheLimit
        ]);

        $this->logger->info("Done");
    }

}
