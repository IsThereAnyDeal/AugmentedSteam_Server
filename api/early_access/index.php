<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();

$select = \dibi::query("SELECT [appid] FROM [early_access]");

$apppids = [];
foreach($select as $a) {
    $appids[$a['appid']] = $a['appid'];
}

$response
    ->data($appids)
    ->respond();
