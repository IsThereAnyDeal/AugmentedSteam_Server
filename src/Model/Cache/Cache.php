<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Cache;

use AugmentedSteam\Server\Database\TCache;
use AugmentedSteam\Server\Model\DataObjects\DCache;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\Create\SqlInsertQuery;

class Cache
{
    private DbDriver $db;
    private TCache $c;

    private SqlInsertQuery $insert;

    public function __construct(DbDriver $db) {
        $this->db = $db;
        $this->c = new TCache();

        $c = $this->c;
        $this->insert = $this->db->insert($c)
            ->columns($c->appid, $c->key, $c->json, $c->timestamp)
            ->onDuplicateKeyUpdate($c->json, $c->timestamp);
    }

    public function getValue(int $appid, int $key, int $expireSeconds) {
        $c = $this->c;

        $value = (new SqlSelectQuery($this->db,
            "SELECT $c->json
            FROM $c
            WHERE $c->appid=:appid
              AND $c->key=:key
              AND $c->timestamp >= :timestamp"
        ))->params([
            ":appid" => $appid,
            ":key" => $key,
            ":timestamp" => time() - $expireSeconds
        ])->fetchValue();

        if (is_null($value)) {
            return null;
        }
        return json_decode($value, true);
    }

    public function setValue(int $appid, int $key, string $json): void {

        $this->insert->persist(
            (new DCache())
                ->setAppid($appid)
                ->setKey($key)
                ->setJson($json)
                ->setTimestamp(time())
        );
    }
}
