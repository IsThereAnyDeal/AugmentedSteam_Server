<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces\AppData;

use AugmentedSteam\Server\Data\Objects\SteamPeak\SteamPeekResults;

interface SteamPeekProviderInterface extends AppDataProviderInterface {
    public function fetch(int $appid): ?SteamPeekResults;
}
