<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("users_profiles")]
class TUsersProfiles extends Table
{
    public Column $steam64;
    public Column $bg_img;
    public Column $bg_appid;
    public Column $style;
    public Column $update_time;
}
