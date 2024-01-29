<?php
namespace AugmentedSteam\Server\Lib\Redis;

enum ERedisKey: string {
    case EarlyAccess = "ea";

    case SteamIdGameMap = "sigm";
}
