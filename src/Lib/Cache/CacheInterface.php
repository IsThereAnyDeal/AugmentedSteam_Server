<?php
namespace AugmentedSteam\Server\Lib\Cache;

interface CacheInterface
{
    public function has(ECacheKey $key, string $field): bool;

    /**
     * @template T
     * @return T|null
     */
    public function get(ECacheKey $key, string $field): mixed;

    /**
     * @template T
     * @param T $data
     */
    public function set(ECacheKey $key, string $field, mixed $data, int $ttl): void;
}
