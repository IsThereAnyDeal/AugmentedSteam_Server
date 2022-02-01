<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TMarketIndex extends Table
{
    public Column $appid;
    public Column $last_update;
    public Column $last_request;
    public Column $request_counter;

    public function __construct(string $alias = "") {
        parent::__construct("market_index", [], $alias);
    }
}
