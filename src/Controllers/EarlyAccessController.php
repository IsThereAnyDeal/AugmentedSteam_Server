<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\EarlyAccessProviderInterface;
use AugmentedSteam\Server\Lib\Cache\CacheInterface;
use AugmentedSteam\Server\Lib\Cache\ECacheKey;
use Psr\Http\Message\ServerRequestInterface;

class EarlyAccessController extends Controller {

    private const int TTL = 3600;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly EarlyAccessProviderInterface $provider
    ) {}

    /**
     * @return list<int>
     */
    public function appids_v1(ServerRequestInterface $request): array {
        $key = ECacheKey::EarlyAccess;
        $field = "ea";

        if ($this->cache->has($key, $field)) {
            $cached = $this->cache->get($key, $field) ?? [];
            if (!is_array($cached) || !array_is_list($cached)) {
                throw new \Exception();
            }
            return $cached;
        }

        $appids = $this->provider->fetch();
        $this->cache->set($key, $field, $appids, self::TTL);
        return $appids;
    }
}
