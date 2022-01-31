<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TGameDlc extends Table
{
    public Column $appid;
    public Column $dlc_category;
    public Column $score;

    public function __construct(string $alias = "") {
        parent::__construct("game_dlc", [], $alias);
    }
}
