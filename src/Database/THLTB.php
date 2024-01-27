<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("hltb")]
class THLTB extends Table
{
    public Column $id;
    public Column $appid;
    public Column $main;
    public Column $extra;
    public Column $complete;
    public Column $found_timestamp;
    public Column $checked_timestamp;
}
