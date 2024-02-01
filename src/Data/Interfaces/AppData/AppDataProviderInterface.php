<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces\AppData;

/**
 * @template T
 */
interface AppDataProviderInterface {

    /**
     * @return T
     */
    public function fetch(int $appid): mixed;
}
