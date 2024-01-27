<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("dlc_categories")]
class TDlcCategories extends Table
{
    public Column $id;
    public Column $name;
    public Column $icon;
    public Column $description;
}
