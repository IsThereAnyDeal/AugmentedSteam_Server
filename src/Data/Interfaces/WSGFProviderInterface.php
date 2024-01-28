<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces;

use AugmentedSteam\Server\Data\Objects\WSGF;

interface WSGFProviderInterface {
    public function fetch(int $appid): ?WSGF;
}
