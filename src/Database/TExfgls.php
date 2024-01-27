<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("exfgls")]
class TExfgls extends Table
{
    public Column $appid;
    public Column $excluded;
    public Column $checked;
    public Column $timestamp;
}
