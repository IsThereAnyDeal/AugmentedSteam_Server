<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TSteamRep extends Table
{
    public Column $steam64;
    public Column $rep;
    public Column $timestamp;
    public Column $checked;

    public function __construct(string $alias = "") {
        parent::__construct("steamrep", [], $alias);
    }
}
