<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("cache")]
class TCache extends Table
{
    public Column $key;
    public Column $field;
    public Column $data;
    public Column $expiry;
}
