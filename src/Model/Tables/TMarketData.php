<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TMarketData extends Table
{
    public Column $hash_name;
    public Column $appid;
    public Column $name;
    public Column $sell_listings;
    public Column $sell_price_usd;
    public Column $icon_url;
    public Column $type;
    public Column $rarity;
    public Column $timestamp;

    public function __construct(string $alias = "") {
        parent::__construct("market_data", [], $alias);
    }
}
