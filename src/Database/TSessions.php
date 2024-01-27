<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("sessions")]
class TSessions extends Table
{
    public Column $token;
    public Column $hash;
    public Column $steam_id;
    public Column $expiry;
}
