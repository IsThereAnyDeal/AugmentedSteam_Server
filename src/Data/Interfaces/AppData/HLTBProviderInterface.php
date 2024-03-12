<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Interfaces\AppData;

use AugmentedSteam\Server\Data\Objects\HLTB;

interface HLTBProviderInterface extends AppDataProviderInterface {
    public function fetch(int $appid): ?HLTB;
}
