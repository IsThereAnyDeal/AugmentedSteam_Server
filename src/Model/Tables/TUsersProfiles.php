<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TUsersProfiles extends Table
{
    public Column $steam64;
    public Column $bg_img;
    public Column $bg_appid;
    public Column $style;
    public Column $update_time;

    public function __construct(string $alias = "") {
        parent::__construct("users_profiles", [], $alias);
    }
}
