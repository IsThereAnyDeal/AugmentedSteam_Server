<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Managers;

use AugmentedSteam\Server\Data\Interfaces\GameIdsProviderInterface;
use AugmentedSteam\Server\Lib\Redis\ERedisKey;
use AugmentedSteam\Server\Lib\Redis\RedisClient;

class GameIdsManager
{
    private const int CacheExpiry = 12*3600;

    public function __construct(
        private readonly RedisClient $redis,
        private readonly GameIdsProviderInterface $provider
    ) {}

    /**
     * @param list<string> $ids
     * @return array<string, string>
     */
    public function getIdMap(array $ids): array {
        $gameMapKey = ERedisKey::SteamIdGameMap->value;

        /** @var array<string, string> $idMap SteamID:Gid */
        $idMap = [];

        /** @var list<string> $idsToFetch */
        $idsToFetch = [];

        /** @var list<string> $gids */
        $gids = $this->redis->hmget($gameMapKey, $ids);
        foreach($gids as $i => $gid) {
            $steamId = $ids[$i];
            $idMap[$steamId] = $gid;

            if (is_null($gid)) {
                $idsToFetch[] = $steamId;
            }
        }

        if (!empty($idsToFetch)) {
            $fetched = $this->provider->fetch($idsToFetch);

            foreach($fetched as $steamId => $gid) {
                $idMap[$steamId] = $gid;
            }

            $this->redis->hmset($gameMapKey, $fetched);
            $this->redis->expire($gameMapKey, self::CacheExpiry, "NX");
        }

        return $idMap;
    }
}
