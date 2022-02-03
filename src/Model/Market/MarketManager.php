<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Market;

use AugmentedSteam\Server\Model\DataObjects\DMarketData;
use AugmentedSteam\Server\Model\Tables\TMarketData;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;

class MarketManager
{
    private DbDriver $db;

    public function __construct(DbDriver $db) {
        $this->db = $db;
    }

    public function doesBackgroundExist(int $appid, string $img): bool {

        $d = new TMarketData();
        return (new SqlSelectQuery($this->db,
            "SELECT 1
            FROM $d
            WHERE $d->appid=:appid
              AND $d->img=:img
              AND $d->type='background'"
        ))->exists([
            ":appid" =>$appid,
            ":img" => $img
        ]);
    }

    /**
     * @param int $appid
     * @return iterable<DMarketData>
     */
    public function getBackgrounds(int $appid): iterable {

        $d = new TMarketData();
        return (new SqlSelectQuery($this->db,
            "SELECT $d->name, $d->img
            FROM $d
            WHERE $d->appid=:appid AND $d->type='background'
            ORDER BY $d->name ASC"
        ))->params([
            ":appid" => $appid
        ])->fetch(DMarketData::class);
    }

    public function getGames(): array {
        $d = new TMarketData();
        return (new SqlSelectQuery($this->db,
            "SELECT DISTINCT $d->appid, $d->appname
            FROM $d
            WHERE $d->type='background'
            ORDER BY $d->appname ASC"
        ))->fetch()
          ->map(fn($o) => [(int)$o->appid, $o->appname])
          ->toArray();
    }
}
