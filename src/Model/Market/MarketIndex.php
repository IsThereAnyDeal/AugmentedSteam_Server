<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Market;

use AugmentedSteam\Server\Model\DataObjects\DMarketIndex;
use AugmentedSteam\Server\Model\Tables\TMarketIndex;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;

class MarketIndex
{
    private DbDriver $db;

    public function __construct(DbDriver $db) {
        $this->db = $db;
    }

    public function recordRequest(int ...$appids): void {
        $appids = array_unique($appids);

        $i = new TMarketIndex();
        $insert = (new SqlInsertQuery($this->db, $i))
            ->columns($i->appid, $i->last_request)
            ->onDuplicateKeyUpdate($i->last_request)
            ->onDuplicateKeyExpression($i->request_counter, "$i->request_counter+1");

        foreach($appids as $appid) {
            $insert->stack(
                (new DMarketIndex())
                    ->setAppid($appid)
                    ->setLastRequest(time())
            );
        }
        $insert->persist();
    }
}
