<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\SteamRep;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Database\TSteamRep;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\DataObjects\DSteamRep;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlDeleteQuery;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Log\LoggerInterface;

class SteamRepManager {
    private const SuccessCacheLimit = 7*86400;
    private const FailureCacheLimit = 86400;

    private DbDriver $db;
    private SimpleLoader $loader;
    private EndpointsConfig $config;
    private LoggerInterface $logger;

    private TSteamRep $r;

    public function __construct(DbDriver $db, SimpleLoader $loader, EndpointsConfig $config, LoggerFactoryInterface $loggerFactory) {
        $this->db = $db;
        $this->loader = $loader;
        $this->config = $config;
        $this->logger = $loggerFactory->create("steamrep");

        $this->r = new TSteamRep();
    }

    public function getRep(int $steamId): array {
        $r = $this->r;

        /** @var ?DSteamRep rep */
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
        ])->fetch(DSteamRep::class)
          ->getOne();

        if (is_null($rep)) {
            $rep = $this->getNewRep($steamId);
        }

        return explode(",", $rep->getRep() ?? "");
    }

    private function getNewRep(int $steamId): DSteamRep {
        $url = $this->config->getSteamRepEndpoint($steamId);

        $reputation = null;
        $checked = false;

        $response = $this->loader->get($url);
        if (!is_null($response)) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body, true);

            if (isset($json['steamrep']['reputation']['full'])) {
                $reputation = $json['steamrep']['reputation']['full'];
                $checked = true;
            }
        }

        $data = (new DSteamRep())
            ->setSteam64($steamId)
            ->setRep($reputation)
            ->setTimestamp(time())
            ->setChecked($checked);

        $r = $this->r;
        (new SqlInsertQuery($this->db, $r))
            ->columns($r->steam64, $r->rep, $r->timestamp, $r->checked)
            ->onDuplicateKeyUpdate($r->rep, $r->timestamp, $r->checked)
            ->persist($data);

        $this->cleanup(); // should be done in cron but shouldn't really cause any trouble here anyway

        if ($checked) {
            $this->logger->info((string)$steamId);
        } else {
            $this->logger->error((string)$steamId);
        }

        return $data;
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
