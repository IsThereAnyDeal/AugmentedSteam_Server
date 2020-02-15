<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["appid"]);

$appid = $endpoint->getParam("appid");
if (!is_numeric($appid)) {
    (new \Api\Response())->fail();
}

$response = new \Api\Response();

$select = \dibi::query("SELECT [geforce_now] FROM [streaming] WHERE [appid] = %i LIMIT 1", $appid)->fetch();

if($select === null){
    $response
    ->data([
        "geforce_now" => false
    ])->respond();
} else {
    $response
    ->data([
        "geforce_now" => $select["geforce_now"] === "1"
    ])
    ->respond();
}