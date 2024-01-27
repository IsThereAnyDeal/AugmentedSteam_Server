<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("market_index")]
class TMarketIndex extends Table
{
    public Column $appid;
    public Column $last_update;
    public Column $last_request;
    public Column $request_counter;
}
