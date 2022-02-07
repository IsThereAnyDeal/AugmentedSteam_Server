<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TEarlyAccess extends Table
{
    public Column $appid;
    public Column $timestamp;

    public function __construct(string $alias = "") {
        parent::__construct("earlyaccess", [], $alias);
    }
}
