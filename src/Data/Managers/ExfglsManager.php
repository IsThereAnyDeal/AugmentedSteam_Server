<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Managers;

use AugmentedSteam\Server\Database\DExfgls;
use AugmentedSteam\Server\Database\TExfgls;
use IsThereAnyDeal\Database\DbDriver;

class ExfglsManager
{
    public function __construct(
        private readonly DbDriver $db,
    ) {}

    public function get(int $appid): DExfgls {
        $e = new TExfgls();

        $data = $this->db->select(<<<SQL
            SELECT $e->excluded
            FROM $e
            WHERE $e->appid=:appid
            SQL
        )->params([
            ":appid" => $appid
        ])->fetch(DExfgls::class)
          ->getOne();

        if (is_null($data)) {
            $data = (new DExfgls())
                ->setAppid($appid)
                ->setExcluded(false)
                ->setChecked(false)
                ->setTimestamp(time());

            $this->db->insert($e)
                ->columns($e->appid, $e->excluded, $e->checked, $e->timestamp)
                ->onDuplicateKeyUpdate($e->excluded, $e->checked, $e->timestamp)
                ->persist($data);
        }

        return $data;
    }
}
