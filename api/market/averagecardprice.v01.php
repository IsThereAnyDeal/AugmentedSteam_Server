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
$select = \dibi::query("SELECT avg([price]) as [average], count(*) as [count] FROM [market_data] WHERE [appid]=%i AND [type]='card' %sql", $appid, $foilSQL)->fetch();

$response = new \Api\Response();
if ($select['count'] == 0) {
    $response->fail("no_data_found", "No data found for given appid");
}

$CONV = \Price\Converter::getConverter()
    ->getConversion("USD", $endpoint->getParam("currency"));
$response->data(["average" => $select['average']*$CONV]);
$response->respond();
