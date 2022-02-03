<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TBadges extends Table
{
    public Column $id;
    public Column $title;
    public Column $img;

    public function __construct(string $alias = "") {
        parent::__construct("badges", [], $alias);
    }
}
