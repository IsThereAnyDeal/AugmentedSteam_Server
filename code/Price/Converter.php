<?php
namespace Price;

use GuzzleHttp\Client;

class Converter {

    private const SUPPORTED = [
        "USD", "GBP", "EUR",
        "RUB", "BRL", "JPY",
        "NOK", "IDR", "MYR",
        "PHP", "SGD", "THB",
        "VND", "KRW", "TRY",
        "UAH", "MXN", "CAD",
        "AUD", "NZD", "INR",
        "HKD", "TWD", "CNY",
        "SAR", "ZAR", "AED",
        "CHF", "CLP", "PEN",
        "COP", "UYU", "ILS",
        "PLN", "ARS", "CRC",
        "KZT", "KWD", "QAR"
    ];

    private const UPDATE_TIMESTAMP = 6*60*60;

    private static $instance;

    public static function getConverter(): self {
        if (!isset(self::$instance)) {
            self::$instance = new Converter();
        }
        return self::$instance;
    }

    private $conversions = [];
    private $guzzle = null;

    private function __construct() {
        // intentionally empty
    }

    public function getConversion(string $from, string $to) {
        $from = strtoupper($from);
        $to = strtoupper($to);

        $select = \dibi::query("SELECT [rate], [timestamp] FROM [currency] WHERE [from]=%s AND [to]=%s", $from, $to)->fetch();
        if ($select === false || $select['timestamp']->getTimestamp() < time()-self::UPDATE_TIMESTAMP) {
            return $this->loadConversion($from, $to);
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

    private function loadConversion(string $from, string $to): ?float {
        if (is_null($this->guzzle)) {
            $this->guzzle = new Client();
        }

        $key = $from."_".$to;

        if (!array_key_exists($from, $this->conversions)) {
            $this->conversions[$from] = null;
        }

        try {
            $response = $this->guzzle->request("GET", "https://api.currconv.com/api/v7/convert?q=$key&compact=ultra&apiKey=".\Config::CurrencyConverterApiKey);
            if (!empty($response)) {
                $json = json_decode($response->getBody(), true);
                if (isset($json[$key])) {
                    $rate = $json[$key];
                    $this->conversions[$from][$to] = $rate;
                    \dibi::query("REPLACE INTO [currency] ([from], [to], [rate]) VALUES (%s, %s, %f)", $from, $to, $rate);
                    return $rate;
                }
            }
        } catch(\Exception $e) {
            \Log::channel("exceptions")->info($e->getMessage());
        }

        return null;
    }

    public function updateAll(): void {
        foreach(self::SUPPORTED as $from) {
            foreach(self::SUPPORTED as $to) {
                if ($from == $to) { continue; }
                $this->loadConversion($from, $to);
            }
        }
    }
}
