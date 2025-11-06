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
        private readonly SteamRepProviderInterface $provider // @phpstan-ignore-line
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

        return $obj?->getReputation() ?? [];
    }
}
