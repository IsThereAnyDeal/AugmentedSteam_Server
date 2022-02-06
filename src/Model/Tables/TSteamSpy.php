<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TSteamSpy extends Table
{
    public Column $appid;
    public Column $owners;
    public Column $average_forever;
    public Column $average_2weeks;
    public Column $timestamp;

    public function __construct(string $alias = "") {
        parent::__construct("steamspy", [], $alias);
    }
}
