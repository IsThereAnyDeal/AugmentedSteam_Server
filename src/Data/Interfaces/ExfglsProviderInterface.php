<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

interface ExfglsProviderInterface {
    /**
     * @param list<int> $appids
     * @return array<int, bool>
     */
    public function fetch(array $appids): array;
}
