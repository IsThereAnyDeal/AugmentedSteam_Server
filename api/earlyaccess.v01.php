<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

(new \Api\Endpoint());

$select = \dibi::query("SELECT [appid] FROM [early_access]");

$appids = [];
foreach($select as $a) {
    $appids[$a['appid']] = $a['appid'];
}

(new \Api\Response())
    ->data($appids)
    ->respond();
