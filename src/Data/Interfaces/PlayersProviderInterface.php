<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

use AugmentedSteam\Server\Data\Objects\Players;

interface PlayersProviderInterface {

    public function fetch(int $appid): Players;
}
