<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TTwitchToken extends Table
{
    public Column $token;
    public Column $expiry;

    public function __construct(string $alias = "") {
        parent::__construct("twitch_token", [], $alias);
    }
}
