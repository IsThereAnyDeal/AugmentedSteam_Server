<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TExfgls extends Table
{
    public Column $appid;
    public Column $excluded;
    public Column $checked;
    public Column $timestamp;

    public function __construct(string $alias = "") {
        parent::__construct("exfgls", [], $alias);
    }
}
