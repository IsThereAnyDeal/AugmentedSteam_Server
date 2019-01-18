<?php
namespace Price;

use GuzzleHttp\Client;

class Converter {

    private const UPDATE_TIMESTAMP = 6*60;

    private static $instances = [];

    public static function getConverter(string $from): self {
        $from = strtoupper($from);
        if (strlen($from) != 3) {
            throw new \Exception("Invalid currency code");
        }

        if (!isset(self::$instances[$from])) {
            self::$instances[$from] = new Converter($from);
        }
        return self::$instances[$from];
    }

    private $conversions = [];
    private $guzzle = null;

    private $from;

    private function __construct(string $from) {
        $this->from = $from;

        $select = \dibi::query("SELECT [to], [rate], [timestamp] FROM [currency] WHERE [from]=%s", $this->from);
        foreach($select as $a) {
            $to = $a['to'];
            $rate = $a['rate'];
            /** @var \Dibi\DateTime $time */
            $time = $a['timestamp'];

            $this->conversions[$to] = $rate;

            if ($time->getTimestamp() < time() - self::UPDATE_TIMESTAMP) {
                $this->loadConversion($to);
            }
        }
    }

    public function getConversion(string $to): ?float {
        $to = strtoupper($to);
        if (strlen($to) != 3) {
            throw new \Exception("Invalid currency code");
        }

        if ($this->from == $to) {
            return 1;
        }

        if (array_key_exists($to, $this->conversions)) {
            return $this->conversions[$to];
        }

        return $this->loadConversion($to);
    }

    private function loadConversion(string $to): ?float {
        if (is_null($this->guzzle)) {
            $this->guzzle = new Client();
        }

        $key = $this->from."_".$to;

        if (!array_key_exists($to, $this->conversions)) {
            $this->conversions[$to] = null;
        }

        try {
            $response = $this->guzzle->request("GET", "http://currencyconverterapi.com/api/v3/convert?q=$key&compact=ultra&apiKey=".\Config::CurrencyConverterApiKey);
            if (!empty($response)) {
                $json = json_decode($response->getBody(), true);
                if (isset($json[$key])) {
                    $rate = $json[$key];
                    $this->conversions[$to] = $rate;
                    \dibi::query("INSERT INTO [currency] ([from], [to], [rate]) VALUES (%s, %s, %f)", $this->from, $to, $rate);
                    return $rate;
                }
            }
        } catch(\Exception $e) {
            \Log::channel("exceptions")->info($e->getMessage());
        }

        return null;
    }

}
