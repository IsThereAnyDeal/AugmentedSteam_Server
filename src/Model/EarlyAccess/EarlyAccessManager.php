<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\EarlyAccess;

use AugmentedSteam\Server\Model\DataObjects\DEarlyAccess;
use AugmentedSteam\Server\Model\Tables\TEarlyAccess;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;

class EarlyAccessManager {

    private DbDriver $db;

    public function __construct(DbDriver $db) {
        $this->db = $db;
    }

    public function getAppids(): array {
        $e = new TEarlyAccess();

        return (new SqlSelectQuery($this->db,
            "SELECT $e->appid
            FROM $e"
        ))->fetch(DEarlyAccess::class)
          ->map(fn(DEarlyAccess $o) => $o->getAppid())
          ->toArray();
    }

}
