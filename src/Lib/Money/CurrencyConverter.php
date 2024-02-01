<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\Money;

use AugmentedSteam\Server\Database\DCurrency;
use AugmentedSteam\Server\Database\TCurrency;
use IsThereAnyDeal\Database\DbDriver;

readonly class CurrencyConverter
{
    public function __construct(
        private DbDriver $db
    ) {}

    public function getConversion(string $from, string $to): ?float {
        $from = strtoupper($from);
        $to = strtoupper($to);
        if ($from == $to) {
            return 1;
        }

        $c = new TCurrency();

        $data = $this->db->select(<<<SQL
            SELECT $c->rate
            FROM $c
            WHERE $c->from=:from
              AND $c->to=:to
            SQL
        )->params([
            ":from" => $from,
            ":to" => $to
        ])->fetch(DCurrency::class)
          ->getOne();

        return $data?->getRate();
    }

    /**
     * @param list<string> $currencies
     * @return array<string, array<string, float>>
     */
    public function getAllConversionsTo(array $currencies): array {
        $c = new TCurrency();

        $select = $this->db->select(<<<SQL
            SELECT $c->from, $c->to, $c->rate
            FROM $c
            WHERE $c->to IN :to
            SQL
        )->params([
            ":to" => $currencies
        ])->fetch(DCurrency::class);

        $result = [];
        foreach($select as $o) {
            $result[$o->getFrom()][$o->getTo()] = $o->getRate();
        }
        return $result;
    }
}
