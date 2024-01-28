<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\SteamRep;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Database\TSteamRep;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Model\DataObjects\DSteamRep;
use IsThereAnyDeal\Database\DbDriver;

class SteamRepManager {
    private const int SuccessCacheLimit = 7*86400;
    private const int FailureCacheLimit = 86400;

    private readonly TSteamRep $r;

    public function __construct(
        private readonly DbDriver $db,
        private readonly SimpleLoader $loader,
        private readonly EndpointsConfig $config
    ) {
        $this->r = new TSteamRep();
    }

    /**
     * @return list<string>
     */
    public function getReputation(int $steamId): array {
        $r = $this->r;

        /** @var ?string $reputation */
        $reputation = $this->db->select(<<<SQL
            SELECT $r->rep
            FROM $r
            WHERE $r->steam64=:steamId
              AND (($r->checked=1 AND $r->timestamp >= :successTimestamp)
                OR ($r->checked=0 AND $r->timestamp >= :failureTimestamp))
            SQL
        )->params([
            ":steamId" => $steamId,
            ":successTimestamp" => time() - self::SuccessCacheLimit,
            ":failureTimestamp" => time() - self::FailureCacheLimit
        ])->fetchValue();

        if (is_null($reputation)) {
            $reputation = $this->getNewReputation($steamId);
        }

        if (empty($reputation)) {
            return [];
        }

        return explode(",", $reputation);
    }

    private function getNewReputation(int $steamId): ?string {
        $url = $this->config->getSteamRepEndpoint($steamId);

        $reputation = null;
        $checked = false;

        $response = $this->loader->get($url);
        if (!is_null($response)) {
            $body = $response->getBody()->getContents();
            $json = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

            if (isset($json['steamrep']['reputation']['full'])) {
                $checked = true;
                $reputation = $json['steamrep']['reputation']['full'];
                if (empty($reputation)) {
                    $reputation = null;
                }
            }
        }

        $r = $this->r;
        $this->db->insert($r)
            ->columns($r->steam64, $r->rep, $r->timestamp, $r->checked)
            ->onDuplicateKeyUpdate($r->rep, $r->timestamp, $r->checked)
            ->persist((new DSteamRep())
                ->setSteam64($steamId)
                ->setRep($reputation)
                ->setTimestamp(time())
                ->setChecked($checked)
            );

        return $reputation;
    }
}
