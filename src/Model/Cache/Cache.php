<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Cache;

use AugmentedSteam\Server\Database\TCache;
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

    /**
     * @return array|null|false  TODO this return type is _too_ PHP
     */
    public function get(int $appid, ECacheKey $key, int $expireSeconds): array|null|false {
        $c = $this->c;

        /** @var DCache $cached */
        $cached = $this->db->select(<<<SQL
            SELECT $c->json
            FROM $c
            WHERE $c->appid=:appid
              AND $c->key=:key
              AND $c->timestamp >= :timestamp
            SQL
        )->params([
            ":appid" => $appid,
            ":key" => $key,
            ":timestamp" => time() - $expireSeconds
        ])->fetch(DCache::class)
          ->getOne();

        if (is_null($cached)) {
            return false;
        }

        $value = $cached->getJson();
        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            return false;
        }
        return $data;
    }

    /**
     * @param ?array<mixed> $data
     */
    public function set(int $appid, ECacheKey $key, ?array $data): void {

        $this->insert->persist(
            (new DCache())
                ->setAppid($appid)
                ->setKey($key)
                ->setJson(json_encode($data, flags: JSON_THROW_ON_ERROR))
                ->setTimestamp(time())
        );
    }
}
