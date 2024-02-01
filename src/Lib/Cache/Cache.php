<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\Cache;

use AugmentedSteam\Server\Database\TCache;
use IsThereAnyDeal\Database\DbDriver;

class Cache implements CacheInterface
{
    private readonly DbDriver $db;
    private readonly TCache $c;

    public function __construct(DbDriver $db) {
        $this->db = $db;
        $this->c = new TCache();
    }

    #[\Override]
    public function has(ECacheKey $key, string $field): bool {
        $c = $this->c;

        return $this->db->select(<<<SQL
                SELECT 1
                FROM $c
                WHERE $c->key=:key
                  AND $c->field=:field
                  AND $c->expiry > UNIX_TIMESTAMP()
                SQL
            )->exists([
                ":key" => $key,
                ":field" => $field
            ]);
        }

    /**
     * @template T
     * @return T|null
     */
    #[\Override]
    public function get(ECacheKey $key, string $field): mixed {
        $c = $this->c;

        $data = $this->db->select(<<<SQL
            SELECT $c->data
            FROM $c
            WHERE $c->key=:key
              AND $c->field=:field
              AND $c->expiry > UNIX_TIMESTAMP()
            SQL
        )->fetchValue([
            ":key" => $key,
            ":field" => $field
        ]);

        if (!is_null($data)) {
            try {
                return igbinary_unserialize($data);
            } catch(\Throwable) {
                $this->db->delete(<<<SQL
                    DELETE FROM $c
                    WHERE $c->key=:key
                      AND $c->field=:field
                    SQL
                )->delete([
                    ":key" => $key,
                    ":field" => $field
                ]);
            }
        }

        return null;
    }

    #[\Override]
    public function set(ECacheKey $key, string $field, mixed $data, int $ttl): void {
        $c = $this->c;

        $this->db->insert($c)
            ->columns($c->key, $c->field, $c->data, $c->expiry)
            ->onDuplicateKeyUpdate($c->data, $c->expiry)
            ->persist((new DCache())
                ->setKey($key)
                ->setField($field)
                ->setData(igbinary_serialize($data))
                ->setExpiry(time()+$ttl)
            )
        ;
    }
}
