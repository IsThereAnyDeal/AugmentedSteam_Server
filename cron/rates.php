<?php
require_once __DIR__ . "/../code/autoloader.php";

$db = \Core\Database::connect();

$logger = Log::channel("cron");
$logger->info("Start rates update");

$guzzle = new GuzzleHttp\Client();

$data = [];

$url = "https://api.isthereanydeal.com/v01/augmentedsteam/rates/?".http_build_query([
        "key" => Config::IsThereAnyDealKey,
    ]);
$result = \Core\Load::load($url);
$json = json_decode($result, true);

if (!isset($json['data'])) {
    throw new \Exception("No data");
}

$data = $json['data'];

$stack = new \Database\Stack(10, "currency", [
    "from", "to", "rate", "timestamp"
]);
$stack->onDuplicateKeyUpdate("rate", "timestamp");

foreach($data as $a) {
    $stack->stack([
        "from" => $a['from'],
        "to" => $a['to'],
        "rate" => (float)$a['rate'],
        "timestamp" => date("Y-m-d H:i:s", $a['timestamp']),
    ]);
}

\dibi::query("DELETE FROM [currency] WHERE [timestamp] < %s", date("Y-m-d H:i:s", time() - 86400));

$logger->info("Finish rates update");
