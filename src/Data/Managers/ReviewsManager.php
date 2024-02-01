<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Managers;

use AugmentedSteam\Server\Data\Interfaces\ReviewsProviderInterface;
use AugmentedSteam\Server\Data\Objects\Reviews\Reviews;
use AugmentedSteam\Server\Model\Cache\Cache;
use AugmentedSteam\Server\Model\Cache\ECacheKey;

class ReviewsManager
{
    private const int CacheLimit = 86400;

    public function __construct(
        private readonly Cache $cache,
        private readonly ReviewsProviderInterface $provider
    ) {}

    public function getData(int $appid): Reviews {
        return $this->provider->fetch($appid);
        /*
        $data = $this->cache->get($appid, ECacheKey::Reviews, self::CacheLimit);

        if (is_null($data)) {
            $data = $this->refresh($appid);
        }

        return empty($data)
            ? null
            : new ReviewsData($data);
        */
    }

}
