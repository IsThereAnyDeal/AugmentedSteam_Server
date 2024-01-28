<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Managers;

use AugmentedSteam\Server\Data\Interfaces\WSGFProviderInterface;
use AugmentedSteam\Server\Data\Objects\WSGF;
use AugmentedSteam\Server\Model\Cache\Cache;
use AugmentedSteam\Server\Model\Cache\ECacheKey;

class WSGFManager
{
    private const int CacheLimit = 3*86400;

    public function __construct(
        private readonly Cache $cache,
        private readonly WSGFProviderInterface $provider
    ) {}

    public function getData(int $appid): ?WSGF {
        // TODO I don't like this cache design, for now it's kept for backwards compatibility and simplicity
        $cached = $this->cache->get($appid, ECacheKey::WSGF, self::CacheLimit);

        $wsgf = null;
        if ($cached === false) {
            $wsgf = $this->provider->fetch($appid);
            $this->cache->set($appid, ECacheKey::WSGF, $wsgf?->toArray());
        } elseif (is_array($cached)) {
            $wsgf = new WSGF();
            $wsgf->fromArray($cached);
        }

        return $wsgf;
    }
}
