<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\StorePage;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\DataObjects\DSteamSpy;
use AugmentedSteam\Server\Model\Tables\TSteamSpy;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Log\LoggerInterface;

class SteamSpyManager
{
    private const CacheLimit = 86400;

    private DbDriver $db;
    private SimpleLoader $loader;
    private LoggerInterface $logger;
    private ProxyFactoryInterface $proxyFactory;
    private EndpointsConfig $config;

    private TSteamSpy $s;

    public function __construct(
        DbDriver $db,
        SimpleLoader $loader,
        LoggerFactoryInterface $loggerFactory,
        ProxyFactoryInterface $proxyFactory,
        EndpointsConfig $config
    ) {
        $this->db = $db;
        $this->loader = $loader;
        $this->logger = $loggerFactory->createLogger("steamspy");
        $this->proxyFactory = $proxyFactory;
        $this->config = $config;

        $this->s = new TSteamSpy();
    }

    public function getData(int $appid): ?DSteamSpy {
        $s = $this->s;

        $data = (new SqlSelectQuery($this->db,
            "SELECT $s->owners, $s->average_forever, $s->average_2weeks, $s->timestamp
            FROM $s
            WHERE $s->appid=:appid
              AND $s->timestamp >= :timestamp"
        ))->params([
            ":appid" => $appid,
            ":timestamp" => time() - self::CacheLimit
        ])->fetch(DSteamSpy::class)
          ->getOne();

        if (is_null($data)) {
            $data = $this->refresh($appid);
        }

        return $data;
    }

    private function refresh(int $appid): DSteamSpy {
        $url = $this->config->getSteamSpyEndpoint($appid);

        $hasData = false;
        $data = (new DSteamSpy())
            ->setAppid($appid)
            ->setOwners("")
            ->setAverage2weeks(0)
            ->setAverageForever(0)
            ->setTimestamp(time());

        $proxy = $this->proxyFactory->createProxy();
        $response = $this->loader->get($url, $proxy->getCurlOptions());
        if (!is_null($response)) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($json['owners'])) {
                $hasData = true;
                $data
                    ->setOwners($json['owners'])
                    ->setAverage2weeks($json['average_2weeks'])
                    ->setAverageForever($json['average_forever']);
            }
        }

        $s = $this->s;
        (new SqlInsertQuery($this->db, $s))
            ->columns($s->appid, $s->owners, $s->average_2weeks, $s->average_forever, $s->timestamp)
            ->onDuplicateKeyUpdate($s->owners, $s->average_2weeks, $s->average_forever, $s->timestamp)
            ->persist($data);

        if ($hasData) {
            $this->logger->info((string)$appid);
        } else {
            $this->logger->error((string)$appid);
        }
        return $data;
    }
}
