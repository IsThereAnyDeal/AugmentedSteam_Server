<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

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

    public function __construct(string $alias = "") {
        parent::__construct("surveys", [], $alias);
    }
}
