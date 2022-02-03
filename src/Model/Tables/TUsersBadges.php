<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TUsersBadges extends Table
{
    public Column $steam64;
    public Column $badge_id;

    public function __construct(string $alias = "") {
        parent::__construct("users_badges", [], $alias);
    }
}
