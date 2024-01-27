<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("steamspy")]
class TSteamSpy extends Table
{
    public Column $appid;
    public Column $owners;
    public Column $average_forever;
    public Column $average_2weeks;
    public Column $timestamp;
}
