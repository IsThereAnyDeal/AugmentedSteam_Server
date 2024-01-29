<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

interface EarlyAccessProviderInterface {
    /** @return list<int> */
    public function fetch(): array;
}
