<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\EarlyAccessProviderInterface;
use AugmentedSteam\Server\Lib\Cache\CacheInterface;
use AugmentedSteam\Server\Lib\Cache\ECacheKey;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class EarlyAccessController extends Controller {

    private const int TTL = 3600;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly EarlyAccessProviderInterface $provider
    ) {}

    public function appids_v1(ServerRequestInterface $request): JsonResponse {
        $key = ECacheKey::EarlyAccess;
        $field = "ea";

        $appids = null;
        if ($this->cache->has($key, $field)) {
            $appids = $this->cache->get($key, $field) ?? [];
            if (!is_array($appids) || !array_is_list($appids)) {
                throw new \Exception();
            }
        }

        if (empty($appids)) {
            $appids = $this->provider->fetch();
            $this->cache->set($key, $field, $appids, self::TTL);
        }

        return (new JsonResponse($appids))
            ->withHeader("Cache-Control", "max-age=3600, public");;
    }
}
