<?php
require_once __DIR__ . "/../../../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["profile"]);

$selected = \dibi::query("SELECT [appid]
                          FROM [profile_users]
                          WHERE [steam64]=%i",
    $endpoint->getParam("profile"))->fetchSingle();

$data = [];
$select = \dibi::query("SELECT DISTINCT [title], [appid]
                        FROM [market_data]
                        WHERE [type]='background'
                        ORDER BY [title] ASC");
foreach($select as $a) {
    $data[] = [
        "t" => $a['title'],
        "id" => $a['appid'],
        "sel" => $a['appid'] == $selected
    ];
}

(new \Api\Response())
    ->data($data)
    ->respond();
