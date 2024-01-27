<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("steamcharts")]
class TSteamCharts extends Table
{
    public Column $appid;
    public Column $recent;
    public Column $peak_day;
    public Column $peak_all;
    public Column $timestamp;
}
