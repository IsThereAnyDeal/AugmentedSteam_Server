<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TCurrency extends Table
{
    public Column $from;
    public Column $to;
    public Column $rate;
    public Column $timestamp;

    public function __construct(string $alias = "") {
        parent::__construct(
            "currency",
            ["from", "to", "rate", "timestamp"],
            $alias
        );
    }
}
