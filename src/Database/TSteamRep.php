<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("steamrep")]
class TSteamRep extends Table
{
    public Column $steam64;
    public Column $rep;
    public Column $timestamp;
    public Column $checked;
}
