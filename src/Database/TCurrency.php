<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("currency")]
class TCurrency extends Table
{
    public Column $from;
    public Column $to;
    public Column $rate;
    public Column $timestamp;
}
