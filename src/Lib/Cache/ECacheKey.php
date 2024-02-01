<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\Cache;

enum ECacheKey: int
{
    case WSGF = 1;
    case Reviews = 2;
    case SteamPeek = 3;
    case Players = 4;
    case Twitch = 5;
    case EarlyAccess = 6;
}
