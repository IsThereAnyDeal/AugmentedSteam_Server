<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

use AugmentedSteam\Server\Data\Objects\TwitchStream;

interface TwitchProviderInterface {
    public function getStream(string $channel): ?TwitchStream;
}
