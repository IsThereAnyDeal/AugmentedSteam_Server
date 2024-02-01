<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Managers;

use AugmentedSteam\Server\Data\Objects\HLTB;
use AugmentedSteam\Server\Database\DHLTB;
use AugmentedSteam\Server\Database\THLTB;
use IsThereAnyDeal\Database\DbDriver;

readonly class HLTBManager {

    public function __construct(
        private DbDriver $db,
    ) {}

    public function get(int $appid): ?HLTB {
        $h = new THLTB();

        /** @var ?DHLTB $data */
        $data = $this->db->select(<<<SQL
            SELECT $h->id, $h->main, $h->extra, $h->complete, $h->checked_timestamp
            FROM $h
            WHERE $h->appid=:appid
            SQL
        )->params([
            ":appid" => $appid
        ])->fetch(DHLTB::class)
          ->getOne();

        if (empty($data)) {
            return null;
        }

        $hltb = new HLTB();
        $hltb->story = $data->getMain();
        $hltb->extras = $data->getExtra();
        $hltb->complete = $data->getComplete();
        $hltb->url = "https://howlongtobeat.com/game/{$data->getId()}";
        return $hltb;
    }
}
