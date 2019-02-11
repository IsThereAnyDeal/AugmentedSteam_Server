<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params([
        "currency",
        "appid"
    ]);

$appid = $endpoint->getParamAsInt("appid");

$select = \dibi::query("SELECT [game], [name], [img], [url], [price]
                        FROM [market_data]
                        WHERE [appid]=%i AND [type]='card'", $appid);

$CONV = \Price\Converter::getConverter()->getConversion("USD", $endpoint->getParam("currency"));

$data = [];
foreach($select as $a) {
    $data[$a['name']] = [
        "game" => $a['game'],
        "img" => $a['img'],
        "url" => $a['url'],
        "price" => $a['price']*$CONV,
    ];
}

(new Api\Response())
    ->data($data)
    ->respond();
