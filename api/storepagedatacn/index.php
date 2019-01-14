<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();

header('Content-Type: application/json; charset=utf-8');

$appid = mysql_real_escape_string($_GET['appid']);

$current_time = time();

// Open database connection
mysql_query("SET character_set_results=utf8", $con)or die(mysql_error());
mb_language('uni');
mb_internal_encoding('UTF-8');

function GetNewValue($the_appid, $connection) {
    $url = Config::KeyLOLEndpoint."appId=".$the_appid;
    $file_headers = @get_headers($url);
    switch ($file_headers[0]) {
        case 'HTTP/1.0 500 Internal Server Error':
            $up = false;
            break;
        case 'HTTP/1.0 429 Unknown';
            $up = false;
            break;
        case 'HTTP/1.1 404 Not Found';
            $up = false;
            break;
        default:
            $up = true;
    }

    if ($up) {
        $filestring = file_get_contents($url);

        mysql_query("SET character_set_client=utf8", $connection)or die(mysql_error());
        mysql_query("SET character_set_connection=utf8", $connection)or die(mysql_error());
        $sql = "INSERT INTO `steamcn` (`appid`, `json`) VALUES (".mysql_real_escape_string($the_appid).", '".mysql_real_escape_string($filestring)."')";
        $result = mysql_query($sql, $connection);
        return $filestring;
    }
}

if (is_numeric($appid)) {
    // Get data
    mysql_query("SET character_set_client=utf8", $con)or die(mysql_error());
        mysql_query("SET character_set_connection=utf8", $con)or die(mysql_error());
    $result = mysql_query("SELECT * FROM steamcn WHERE appid='".$appid."' LIMIT 1", $con);
    $num_rows = mysql_num_rows($result);

    // if cached, return the database value
    if ($num_rows > 0) {
        while ($row = mysql_fetch_array($result)) {
            $access_time = strtotime($row['access_time']);

            if ($current_time - $access_time >= 3600) {
                $sql = "DELETE FROM `steamcn` WHERE `appid` = ".mysql_real_escape_string($appid);
                $result = mysql_query($sql, $con);
                $return = GetNewValue($appid, $con);
            } else {
                $return = $row['json'];
            }
        }

    // if not cached or expired, get new value
    } else {
        $return = GetNewValue($appid, $con);
    }

    // Output data
    if (strlen($return) > 0) {
        echo $return;
    } else {
        echo '{"success": false}';
    }
}

exit;
