<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

use AugmentedSteam\Server\Data\Objects\Prices;

interface PricesProviderInterface {

    /**
     * @param list<string> $steamIds
     * @param list<int> $shops
     */
    public function fetch(array $steamIds, array $shops, string $country): ?Prices;
}
