<?php
require_once __DIR__ . "/../../../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint());

$data = [];
$select = \dibi::query("SELECT DISTINCT [title], [appid]
                        FROM [market_data]
                        WHERE [type]='background'
                        ORDER BY [title] ASC");
foreach($select as $a) {
    $data[] = [$a['appid'], $a['title']];
}

(new \Api\Response())
    ->data($data)
    ->respond();
