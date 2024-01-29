<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\EarlyAccessProviderInterface;
use AugmentedSteam\Server\Lib\Redis\ERedisKey;
use AugmentedSteam\Server\Lib\Redis\RedisClient;
use Psr\Http\Message\ServerRequestInterface;

class EarlyAccessController extends Controller {

    private const int TTL = 3600;

    public function __construct(
        private readonly RedisClient $redis,
        private readonly EarlyAccessProviderInterface $provider
    ) {}

    public function getAppids_v1(ServerRequestInterface $request): array {

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
