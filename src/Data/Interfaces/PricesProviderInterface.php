<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

interface PricesProviderInterface {

    /**
     * @param list<string> $gids
     * @param list<int> $shops
     * @return array<mixed>
     */
    public function fetch(array $gids, array $shops, string $country): array;
}
