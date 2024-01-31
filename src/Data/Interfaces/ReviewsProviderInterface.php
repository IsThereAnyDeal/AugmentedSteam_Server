<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

use AugmentedSteam\Server\Data\Objects\Reviews\Reviews;

interface ReviewsProviderInterface {
    public function fetch(int $appid): Reviews;
}
