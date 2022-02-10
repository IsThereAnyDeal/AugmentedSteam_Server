<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Money;

use AugmentedSteam\Server\Model\DataObjects\DCurrency;
use AugmentedSteam\Server\Model\Tables\TCurrency;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;

class CurrencyConverter
{
    private DbDriver $db;

    public function __construct(DbDriver $db) {
        $this->db = $db;
    }

    public function getConversion(string $from, string $to): ?float {
        $from = strtoupper($from);
        $to = strtoupper($to);
        if ($from == $to) {
            return 1;
        }

        $c = new TCurrency();

        /** @var ?DCurrency $data */
        $data = (new SqlSelectQuery($this->db,
            "SELECT $c->rate
            FROM $c
            WHERE $c->from=:from AND $c->to=:to"
        ))->params([
            ":from" => $from,
            ":to" => $to
        ])->fetch(DCurrency::class)
          ->getOne();

        return is_null($data)
            ? null
            : $data->getRate();
    }

    public function getAllConversionsTo(array $list): array {
        $c = new TCurrency();

        $select = (new SqlSelectQuery($this->db,
            "SELECT $c->from, $c->to, $c->rate
            FROM $c
            WHERE $c->to IN :to"
        ))->params([
            ":to" => $list
        ])->fetch(DCurrency::class);

        $result = [];
        /** @var DCurrency $o */
        foreach($select as $o) {
            $result[$o->getFrom()][$o->getTo()] = $o->getRate();
        }

        return $result;
    }
}
