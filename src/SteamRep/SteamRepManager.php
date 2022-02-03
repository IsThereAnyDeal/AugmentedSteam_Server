<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\SteamRep;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\DataObjects\DSteamRep;
use AugmentedSteam\Server\Model\Tables\TSteamRep;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlDeleteQuery;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Log\LoggerInterface;

class SteamRepManager {
    private const SuccessCacheLimit = 7*86400;
    private const FailureCacheLimit = 86400;

    private DbDriver $db;
    private Client $guzzle;
    private EndpointsConfig $config;
    private LoggerInterface $logger;

    private TSteamRep $r;

    public function __construct(DbDriver $db, Client $guzzle, EndpointsConfig $config, LoggerFactoryInterface $loggerFactory) {
        $this->db = $db;
        $this->guzzle = $guzzle;
        $this->config = $config;
        $this->logger = $loggerFactory->createLogger("steamrep");

        $this->r = new TSteamRep();
    }

    public function getRep(int $steamId): array {
        $r = $this->r;

        $rep = (new SqlSelectQuery($this->db,
            "SELECT $r->rep
            FROM $r
            WHERE $r->steam64=:steamId
              AND (($r->checked=1 AND $r->timestamp >= :successTimestamp)
                OR ($r->checked=0 AND $r->timestamp >= :failureTimestamp))"
        ))->params([
            ":steamId" => $steamId,
            ":successTimestamp" => time() - self::SuccessCacheLimit,
            ":failureTimestamp" => time() - self::FailureCacheLimit
        ])->fetchValue();

        if (is_null($rep)) {
            $rep = $this->getNewRep($steamId);
        }

        return explode(",", $rep);
    }

    private function getNewRep(int $steamId): ?string {
        $endpoint = rtrim($this->config->getSteamRepEndpoint(), "/");
        $url = "{$endpoint}/$steamId?json=1";

        $reputation = null;
        $checked = false;

        try {
            $response = $this->guzzle->get($url);
            $body = $response->getBody()->getContents();
            $json = json_decode($body, true);

            if (isset($json['steamrep']['reputation']['full'])) {
                $reputation = $json['steamrep']['reputation']['full'];
                $checked = true;
            }

        } catch (GuzzleException $e) {
            // no handling on guzzle exception
        }

        $r = $this->r;
        (new SqlInsertQuery($this->db, $r))
            ->columns($r->steam64, $r->rep, $r->timestamp, $r->checked)
            ->onDuplicateKeyUpdate($r->rep, $r->timestamp, $r->checked)
            ->persist(
                (new DSteamRep())
                    ->setSteam64($steamId)
                    ->setRep($reputation)
                    ->setTimestamp(time())
                    ->setChecked($checked)
            );

        $this->cleanup(); // should be done in cron but shouldn't really cause any trouble here anyway

        if ($checked) {
            $this->logger->info($steamId);
        } else {
            $this->logger->error($steamId);
        }

        return $reputation;
    }

    private function cleanup(): void {
        $r = $this->r;

        (new SqlDeleteQuery($this->db,
            "DELETE FROM $r
            WHERE ($r->checked=1 AND $r->timestamp < :successTimestamp)
               OR ($r->checked=0 AND $r->timestamp < :failureTimestamp)"
        ))->delete([
            ":successTimestamp" => time() - self::SuccessCacheLimit,
            ":failureTimestamp" => time() - self::FailureCacheLimit
        ]);
    }

}
