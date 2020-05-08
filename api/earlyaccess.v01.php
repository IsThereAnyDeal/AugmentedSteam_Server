<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

(new \Api\Endpoint());
$response = new \Api\Response();

$data = [];
try {
    $url = "https://api.isthereanydeal.com/v01/augmentedsteam/earlyaccess/?".http_build_query([
            "key" => Config::IsThereAnyDealKey,
        ]);
    $result = \Core\Load::load($url);
    $json = json_decode($result, true);

    if (!isset($json['data'])) {
        $response
            ->fail();
    }

    $data = $json['data'];

} catch(\Exception $e) {
    $response
        ->fail();
}

$response
    ->data($data)
    ->respond();
