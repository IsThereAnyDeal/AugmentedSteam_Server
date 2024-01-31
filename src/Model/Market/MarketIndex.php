<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Market;

use AugmentedSteam\Server\Database\TMarketIndex;
use AugmentedSteam\Server\Model\DataObjects\DMarketIndex;
use IsThereAnyDeal\Database\DbDriver;

readonly class MarketIndex
{
    public function __construct(
        private DbDriver $db
    ) {}

    public function recordRequest(int ...$appids): void {
        $appids = array_unique($appids);

        $i = new TMarketIndex();
        $insert = $this->db->insert($i)
            ->columns($i->appid, $i->last_request)
            ->onDuplicateKeyUpdate($i->last_request)
            ->onDuplicateKeyExpression($i->request_counter, "{$i->request_counter->name}+1");

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
