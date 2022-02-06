<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class THLTB extends Table
{
    public Column $id;
    public Column $appid;
    public Column $main;
    public Column $extra;
    public Column $complete;
    public Column $found_timestamp;
    public Column $checked_timestamp;

    public function __construct(string $alias = "") {
        parent::__construct("hltb", [], $alias);
    }
}
