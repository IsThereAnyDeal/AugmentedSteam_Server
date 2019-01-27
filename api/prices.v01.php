<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(
        [],
        [
            "stores" => [],
            "cc" => null,
            "coupon" => null,
            "appids" => [],
            "subids" => [],
            "bundleids" => [],
        ]
    );

$response = new \Api\Response();

$ids = [];
$appids = $endpoint->getParamAsArray("appids");
foreach($appids as $appid) {
    if (empty($appid)) { continue; }
    $ids[] = "app/".$appid;
}

$subids = $endpoint->getParamAsArray("subids");
foreach($subids as $subid) {
    if (empty($subid)) { continue; }
    $ids[] = "sub/$subid";
}

$bundleids = $endpoint->getParamAsArray("bundleids");
foreach($bundleids as $bundleid) {
    if (empty($bundleid)) { continue; }
    $ids[] = "bundle/$bundleid";
}

if (count($ids) == 0) {
    $response->fail();
}

$params = [
    "key" => Config::IsThereAnyDealKey,
    "shop" => "steam",
    "ids" => implode(",", $ids),
];

$country = $endpoint->getParam("cc");
if (!empty($country)) {
    $params['country'] = $country;
}

$stores = $endpoint->getParamAsArray("stores");
if (!empty($stores)) {
    $params['allowed'] = implode(",", $stores);
}

if (!empty($endpoint->getParam("coupon"))) {
    $params['optional'] = "voucher";
}

$data = [];
try {
    $url = "https://api.isthereanydeal.com/v01/game/overview/?".http_build_query($params);
    $result = \Core\Load::load($url);
    $json = json_decode($result, true);

    if (!isset($json['data'])) {
        $response->fail();
    }

    $data['data'] = $json['data'];
    $data['.meta'] = $json['.meta'];

} catch(\Exception $e) {
    $response->fail();
}

$response
    ->data($data)
    ->respond();

