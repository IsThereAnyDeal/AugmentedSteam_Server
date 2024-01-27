<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Database;

use IsThereAnyDeal\Database\Attributes\TableName;
use IsThereAnyDeal\Database\Tables\Column;
use IsThereAnyDeal\Database\Tables\Table;

#[TableName("market_data")]
class TMarketData extends Table
{
    public Column $hash_name;
    public Column $appid;
    public Column $appname;
    public Column $name;
    public Column $sell_listings;
    public Column $sell_price_usd;
    public Column $img;
    public Column $url;
    public Column $type;
    public Column $rarity;
    public Column $timestamp;
}
