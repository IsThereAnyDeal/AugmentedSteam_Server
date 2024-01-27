<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("surveys")]
class TSurveys extends Table
{
    public Column $appid;
    public Column $steamid;
    public Column $framerate;
    public Column $optimized;
    public Column $lag;
    public Column $graphics_settings;
    public Column $bg_sound_mute;
    public Column $good_controls;
    public Column $timestamp;
}
