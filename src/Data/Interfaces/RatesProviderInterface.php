<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

interface RatesProviderInterface {

    /**
     * @return list<array{
     *     from: string,
     *     to: string,
     *     rate: float
     * }>
     */
    public function fetch(): array;
}
