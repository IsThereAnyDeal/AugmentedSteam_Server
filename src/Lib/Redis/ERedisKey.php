<?php
namespace AugmentedSteam\Server\Lib\Redis;

enum ERedisKey: string {
    case ApiThrottleIp = "throttle";

    public function getKey(string $suffix=""): string {
        return empty($suffix)
            ? $this->value
            : "{$this->value}:{$suffix}";
    }
}
