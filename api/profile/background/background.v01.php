<?php
require_once __DIR__ . "/../../../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["appid"]);

$select = \dibi::query("SELECT [appid], [name], [img]
                        FROM [market_data]
                        WHERE [appid]=%i AND [type]='background'
                        ORDER BY [name] ASC", $endpoint->getParamAsInt("appid"));
$data = [];
foreach($select as $a) {

    // to have smaller response, do not use keys, just send array
    $data[] = [
        $a['img'],
        preg_replace("#\s*\(Profile Background\)#", "", $a['name']),
    ];
}

(new \Api\Response())
    ->data($data)
    ->respond();
