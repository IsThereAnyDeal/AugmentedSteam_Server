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

    public function getAllConversionsTo(string $to) {
        $updated = [];

        $select = \dibi::query("SELECT [from], [rate], [timestamp] FROM [currency] WHERE [to]=%s", $to);
        foreach($select as $a) {
            $from = $a['from'];
            $rate = $a['rate'];
            /** @var \Dibi\DateTime $time */
            $time = $a['timestamp'];

            $this->conversions[$from][$to] = $rate;
            $updated[$from] = $time->getTimestamp();
        }

        foreach(self::SUPPORTED as $currency) {
            if (!isset($this->conversions[$currency][$to]) || $updated[$currency] < time() - self::UPDATE_TIMESTAMP) {
                $this->loadConversion($currency, $to);
            }
        }

        $result = [];
        foreach(self::SUPPORTED as $currency) {
            if (!isset($this->conversions[$currency][$to])) { continue; }
            $result[$currency][$to] = $this->conversions[$currency][$to];
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
            $response = $this->guzzle->request("GET", "http://currencyconverterapi.com/api/v3/convert?q=$key&compact=ultra&apiKey=".\Config::CurrencyConverterApiKey);
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

}
