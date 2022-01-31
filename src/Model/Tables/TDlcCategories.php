<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TDlcCategories extends Table
{
    public Column $id;
    public Column $name;
    public Column $icon;
    public Column $description;

    public function __construct(string $alias = "") {
        parent::__construct("dlc_categories", [], $alias);
    }
}
