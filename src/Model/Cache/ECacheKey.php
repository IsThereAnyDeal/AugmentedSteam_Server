<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Cache;

enum ECacheKey: int
{
    case WSGF = 1;
    case Reviews = 2;
    case SteamPeek = 3;
}
