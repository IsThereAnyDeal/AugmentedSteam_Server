<?php
namespace Price;

class Converter {

    private static $instance;

    public static function getConverter(): self {
        if (!isset(self::$instance)) {
            self::$instance = new Converter();
        }
        return self::$instance;
    }

    private $conversions = [];

    private function __construct() {
        // intentionally empty
    }

    public function getConversion(string $from, string $to) {
        $from = strtoupper($from);
        $to = strtoupper($to);

        $select = \dibi::query("SELECT [rate] FROM [currency] WHERE [from]=%s AND [to]=%s", $from, $to)->fetch();
        if ($select === false) {
            throw new \Exception();
        }

        return $select['rate'];
    }

    public function getAllConversionsTo(array $list) {
        $result = [];

        $select = \dibi::query("SELECT [from], [to], [rate] FROM [currency] WHERE [to] IN %in", $list);
        foreach($select as $a) {
            $to   = $a['to'];
            $from = $a['from'];
            $rate = $a['rate'];

            $this->conversions[$from][$to] = $rate;
            $result[$from][$to] = $rate;
        }

        return $result;
    }
}
