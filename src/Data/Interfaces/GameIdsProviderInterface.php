<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

interface GameIdsProviderInterface {

    /**
     * @param list<string> $ids
     * @return array<string, string>
     */
    public function fetch(array $ids): array;
}
