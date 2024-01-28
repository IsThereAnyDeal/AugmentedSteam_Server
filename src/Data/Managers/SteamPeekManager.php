<?php
namespace AugmentedSteam\Server\Data\Managers;

use AugmentedSteam\Server\Data\Interfaces\SteamPeekProviderInterface;
use AugmentedSteam\Server\Data\Objects\SteamPeekGame;
use AugmentedSteam\Server\Data\Objects\SteamPeekResults;
use AugmentedSteam\Server\Model\Cache\Cache;
use AugmentedSteam\Server\Model\Cache\ECacheKey;

class SteamPeekManager {

    private const int CacheLimit = 10*86400;

    public function __construct(
        private readonly Cache $cache,
        private readonly SteamPeekProviderInterface $provider
    ) {}

    /**
     * @param int $appid
     * @param int $preferedCount
     * @param bool $randomOrder
     * @return list<SteamPeekGame>
     */
    public function getSimilar(int $appid, int $preferedCount=5, bool $randomOrder=false): array {
        $cached = $this->cache->get($appid, ECacheKey::SteamPeek, self::CacheLimit);

        $similar = null;
        if ($cached === false) {
            $similar = $this->provider->fetch($appid);
            $this->cache->set($appid, ECacheKey::SteamPeek, $similar?->toArray());
        } elseif (is_array($cached)) {
            $similar = (new SteamPeekResults())->fromArray($cached);
        }

        if (is_null($similar)) {
            return [];
        }

        $games = $similar->games;
        if ($randomOrder) {
            shuffle($games);
        }
        return array_slice($games, 0, $preferedCount);
    }
}
