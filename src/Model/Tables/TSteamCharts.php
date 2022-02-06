<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TSteamCharts extends Table
{
    public Column $appid;
    public Column $recent;
    public Column $peak_day;
    public Column $peak_all;
    public Column $timestamp;

    public function __construct(string $alias = "") {
        parent::__construct("steamcharts", [], $alias);
    }
}
