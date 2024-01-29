<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Managers;

use AugmentedSteam\Server\Data\Interfaces\EarlyAccessProviderInterface;
use AugmentedSteam\Server\Lib\Redis\ERedisKey;
use AugmentedSteam\Server\Lib\Redis\RedisClient;

class EarlyAccessManager {
    private const int TTL = 3600;

    public function __construct(
        private readonly RedisClient $redis,
        private readonly EarlyAccessProviderInterface $provider
    ) {}

    /**
     * @return list<int>
     */
    public function getAppids(): array {

        $cached = $this->redis->get(ERedisKey::EarlyAccess->value);

        $appids = null;
        if (!is_null($cached)) {
            $appids = json_decode($cached, true);
            if (!is_array($appids) || !array_is_list($appids)) {
                $appids = null;
            }
        }

        if (is_null($appids)) {
            $appids = $this->provider->fetch();
            $this->redis->set(ERedisKey::EarlyAccess->value, json_encode($appids), "EX", self::TTL);
        }

        return $appids;
    }
}
