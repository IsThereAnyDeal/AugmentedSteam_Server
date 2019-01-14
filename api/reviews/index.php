<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();


$appid = isset($_GET['appids']) ? $_GET['appids'] : $_POST['appids'];
$appid = mysql_real_escape_string($appid);
$build = "{";

$apps = array_map('intval', explode(',', $appid));

foreach ($apps as $app) {
    if ($app > 0) {
        $result = mysql_query("SELECT * FROM steam_reviews WHERE appid='".$app."' LIMIT 1", $con);
        $num_rows = mysql_num_rows($result);

        // Return the database value
        if ($num_rows > 0) {
            while ($row = mysql_fetch_array($result)) {
                $total = $row["total"];
                $pos   = $row["pos"];
            }
            $build .= "\"" . $app . "\":{\"t\":" . $total . ",\"p\":" . $pos . "},";
        }
    }
}
$build = rtrim(trim($build), ',');
$build .= "}";

echo $build;

exit;
