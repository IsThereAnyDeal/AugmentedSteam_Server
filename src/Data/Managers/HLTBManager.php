<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Managers;

use AugmentedSteam\Server\Data\Objects\DHLTB;
use AugmentedSteam\Server\Database\THLTB;
use IsThereAnyDeal\Database\DbDriver;

readonly class HLTBManager {

    public function __construct(
        private readonly DbDriver $db,
    ) {}

    public function getData(int $appid): ?DHLTB {
        $h = new THLTB();

        return $this->db->select(<<<SQL
            SELECT $h->id, $h->main, $h->extra, $h->complete, $h->checked_timestamp
            FROM $h
            WHERE $h->appid=:appid
            SQL
        )->params([
            ":appid" => $appid
        ])->fetch(DHLTB::class)
          ->getOne();
    }
}
