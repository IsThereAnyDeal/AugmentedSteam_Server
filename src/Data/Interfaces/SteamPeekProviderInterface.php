<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

use AugmentedSteam\Server\Data\Objects\SteamPeekResults;

interface SteamPeekProviderInterface {
    public function fetch(int $appid): ?SteamPeekResults;
}
