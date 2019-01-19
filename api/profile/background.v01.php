<?php
require_once __DIR__ . "/../../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["appid", "profile"]);

$currentSelected = \dibi::query("SELECT [profile_background_img] FROM [profile_users] WHERE [steam64]=%i", $endpoint->getParam("profile"));

$select = \dibi::query("SELECT [appid], [name], [img]
                        FROM [market_data]
                        WHERE [appid]=%i AND [type]='background'
                        ORDER BY [name] ASC", $appid);
$data = [];
foreach($select as $a) {
    $img = "//steamcommunity.com/economy/image/".$a['img'];
    $imgSmall = $img."/252fx160f";

    $data[] = [
        "id" => $imgSmall,
        "name" => preg_replace("#\s*(Profile Background)#", "", $a['name']),
        "index" => $a['img'],
        "selected" => ($a['img'] == $currentSelected)
    ];
}

(new \Api\Response())
    ->data($data)
    ->respond();
