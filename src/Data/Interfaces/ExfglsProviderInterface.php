<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

use AugmentedSteam\Server\Data\Interfaces\AppData\AppDataProviderInterface;

interface ExfglsProviderInterface extends AppDataProviderInterface {

    public function fetch(int $appid): bool;
}
