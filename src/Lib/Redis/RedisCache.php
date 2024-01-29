<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\Redis;

class RedisCache
{
    private const int ZombieLimit = 14*86400;

    public function __construct(
        private readonly RedisClient $redis
    ) {}

    private function data(ERedisKey $key): string {
        return $key->value;
    }

    private function control(ERedisKey $key): string {
        return "{$key->value}:control";
    }

    public function has(ERedisKey $key, string $field): bool {
        $dataKey = $this->data($key);
        $controlKey = $this->control($key);

        $timestamp = time();
        $this->redis->multi();
        $expiredKeys = $this->redis->zrangebyscore($controlKey, "-inf", $timestamp);
        if (!empty($expiredKeys)) {
            $this->redis->hdel($dataKey, $expiredKeys);
            $this->redis->zremrangebyscore($controlKey, "-inf", $timestamp);
        }
        $this->redis->exec();

        return !is_null($this->redis->zscore($dataKey, $field));
    }

    public function get(ERedisKey $key, string $field): ?string {
        $dataKey = $this->data($key);
        $value = $this->redis->hget($dataKey, $field);
        if (is_null($value)) {
            // potential problem, has() has not been called, or control and data are out of sync
            $this->redis->zrem($this->control($key), $field);
        }
        return $value === "null" ? null : $value;
    }

    public function set(ERedisKey $key, string $field, ?string $value, int $expiry) {
        $dataKey = $this->data($key);
        $controlKey = $this->control($key);

        $this->redis->multi();
        $this->redis->hset($dataKey, $field, $value ?? "null");
        $this->redis->zadd($controlKey, [
            $field => time() + $expiry
        ]);
        $this->redis->expire($dataKey, self::ZombieLimit);
        $this->redis->expire($controlKey, self::ZombieLimit);
        $this->redis->exec();
    }
}
