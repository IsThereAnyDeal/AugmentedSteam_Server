<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params([
        "currency"
    ], [
        "appids" => [],
        "foilappids" => [],
    ]);


$appids = $endpoint->getParamAsArray("appids");
$foilAppids = $endpoint->getParamAsArray("foilappids");

if (count($appids) == 0 && count($foilAppids) == 0) {
    (new \Api\Response())
        ->fail();
}

$sql = [];
if (count($appids) != 0) {
    $sql[] = ["[appid] IN %in AND [rarity]!='foil'", $appids];
}

if (count($foilAppids) != 0) {
    $sql[] = ["[appid] IN %in AND [rarity]='foil'", $foilAppids];
}

$select = \dibi::query("SELECT [appid], [rarity]='foil' as [foil], avg([price]) as [average], count(*) as [count]
                        FROM [market_data]
                        WHERE [type]='card' AND (%or)
                        GROUP BY [appid], [rarity]='foil'", $sql);

$result = [];

$CONV = \Price\Converter::getConverter()
    ->getConversion("USD", $endpoint->getParam("currency"));

foreach($select as $a) {
    $cnt = $a['count'];
    if ($cnt == 0) { continue; }

    $appid = $a['appid'];
    $isFoil = $a['foil'];
    $avg = $a['average']*$CONV;

    $result[$appid][$isFoil ? 'foil' : 'regular'] = [
        "average" => $avg
    ];
}

(new \Api\Response())
    ->data($result)
    ->respond();
