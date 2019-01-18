<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["base"]);

$response = new \Api\Response();

$base = $endpoint->getParam("base");
if (strlen($base) != 3) {
    $response->fail();
}

$base = strtoupper($base);

// TODO supported currencies shouldn't be hardcoded here
$supported = [
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

try {
    $converter = \Price\Converter::getConverter($base);
} catch (Exception $e) {
    $response->fail();
}

$data = [
    $base => []
];

foreach($supported as $code) {
    try {
        $data[$base][$code] = $converter->getConversion($code);
    } catch (Exception $e) {
        // ignore currency
    }
}

$response
    ->data($data)
    ->respond();
