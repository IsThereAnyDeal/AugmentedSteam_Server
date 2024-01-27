<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("game_dlc")]
class TGameDlc extends Table
{
    public Column $appid;
    public Column $dlc_category;
    public Column $score;
}
