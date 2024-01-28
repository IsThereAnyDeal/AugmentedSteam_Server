<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

interface SteamRepProviderInterface {

    /**
     * @return ?list<string>
     */
    public function getReputation(int $steamId): ?array;
}
