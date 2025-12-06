<?php
namespace AugmentedSteam\Server\Lib\Redis;

enum ERedisKey: string {
    case ApiThrottleIp = "throttle";
    case PriceOverview = "overview";
    case Gids = "gids";


    public function getKey(string $suffix=""): string {
        return empty($suffix)
            ? $this->value
            : "{$this->value}:{$suffix}";
    }
}
