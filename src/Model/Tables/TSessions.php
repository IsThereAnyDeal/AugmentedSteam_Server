<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Tables;

use IsThereAnyDeal\Database\Sql\Column;
use IsThereAnyDeal\Database\Sql\Table;

class TSessions extends Table
{
    public Column $token;
    public Column $hash;
    public Column $steam_id;
    public Column $expiry;

    public function __construct(string $alias = "") {
        parent::__construct("sessions", [], $alias);
    }
}
