<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces\AppData;

use AugmentedSteam\Server\Data\Objects\Reviews\Reviews;

interface ReviewsProviderInterface extends AppDataProviderInterface {
    public function fetch(int $appid): Reviews;
}
