<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["appid"]);

$appid = $endpoint->getParam("appid");
if (!is_numeric($appid)) {
    (new \Api\Response())->fail();
}

$data = [];
$select = \dibi::query("SELECT [category_name], [category_icon], [category_text], count(*) as [cnt]
                        FROM [gamedata] as g
                        JOIN [dlc_category] as d ON [dlc_category]=[d.id]
                        WHERE [appid]=%i
                        GROUP BY [appid], [dlc_category]
                        ORDER BY [cnt] DESC
                        LIMIT 3", $appid);
foreach($select as $a) {
    $data = [
        "name" => $a['category_name'],
        "icon" => $a['category_icon'],
        "desc" => $a['category_text'],
        "count" => $a['cnt']
    ];
}

(new \Api\Response())
    ->data($data)
    ->respond();
