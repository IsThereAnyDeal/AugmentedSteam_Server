<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Managers;

use AugmentedSteam\Server\Data\Interfaces\TwitchProviderInterface;
use AugmentedSteam\Server\Data\Objects\TwitchStream;
use AugmentedSteam\Server\Lib\Redis\ERedisKey;
use AugmentedSteam\Server\Lib\Redis\RedisCache;

class TwitchManager {
    private const int Expiry = 1800;

    public function __construct(
        private readonly RedisCache $cache,
        private readonly TwitchProviderInterface $provider
    ) {}

    /**
     * @return list<string>
     */
    public function getStream(string $channel): ?TwitchStream {

        if ($this->cache->has(ERedisKey::Twitch, $channel)) {
            $data = $this->cache->get(ERedisKey::Twitch, $channel);
            return is_null($data)
                ? null
                : igbinary_unserialize($data);
        }

        $stream = $this->provider->getStream($channel);
        $this->cache->set(ERedisKey::Twitch,
            $channel,
            is_null($stream) ? null : igbinary_serialize($stream),
            self::Expiry
        );

        return $stream;
    }
}
