<?php
require_once __DIR__ . "/../../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params([
        "appid",
        "currency"
    ], [
        "foil" => false
    ]);


$foilSQL = $endpoint->getParam("foil")
    ? "AND [rarity] = 'foil'"
    : "AND [rarity] != 'foil'";

$appid = $endpoint->getParamAsInt("appid");
$average = \dibi::query("SELECT AVG([price]) FROM [market_data] WHERE [appid]=%i AND [type]='card' %sql", $appid, $foilSQL)->fetchSingle();

$response = new \Api\Response();
if ($average !== false) {
    $CONV = \Price\Converter::getConverter()
        ->getConversion("USD", $endpoint->getParam("currency"));
    $response->data(["average" => $average*$CONV]);
}

$response->respond();
