<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Managers;

use AugmentedSteam\Server\Data\Interfaces\SteamRepProviderInterface;
use AugmentedSteam\Server\Data\Objects\DSteamRep;
use AugmentedSteam\Server\Database\TSteamRep;
use IsThereAnyDeal\Database\DbDriver;

class SteamRepManager {
    private const int SuccessCacheLimit = 7*86400;
    private const int FailureCacheLimit = 86400;

    public function __construct(
        private readonly DbDriver $db,
        private readonly SteamRepProviderInterface $provider
    ) {}

    /**
     * @return list<string>
     */
    public function getReputation(int $steamId): array {
        $r = new TSteamRep();

        /** @var ?DSteamRep $obj */
        $obj = $this->db->select(<<<SQL
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
        ])->fetch(DSteamRep::class)
          ->getOne();

        $reputation = $obj?->getReputation();

        if (is_null($reputation)) {
            $reputation = $this->provider->getReputation($steamId);

            $this->db->insert($r)
                ->columns($r->steam64, $r->rep, $r->timestamp, $r->checked)
                ->onDuplicateKeyUpdate($r->rep, $r->timestamp, $r->checked)
                ->persist((new DSteamRep())
                    ->setSteam64($steamId)
                    ->setReputation($reputation)
                    ->setTimestamp(time())
                );
        }

        return $reputation ?? [];
    }
}
