<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();

	
$appid = mysql_real_escape_string($_GET['appid']);

// gets and returns a new value, storing it in the database
function GetNewValue($the_appid, $connection) {
    $url = Config::SteamSpyEndpoint."&appid=".$the_appid;
    $file_headers = @get_headers($url);
    switch ($file_headers[0]) {
        case 'HTTP/1.0 500 Internal Server Error':
            $up = false;
            break;
        case 'HTTP/1.0 429 Unknown';
            $up = false;
            break;
        default:
            $up = true;
    }

    if ($up) {
        $filestring = file_get_contents($url);

        // Convert JSON results into variables
        $array = json_decode($filestring, true);

        // Then, insert these values into the database
        $sql = "INSERT INTO `steamspy` (`appid`, `owners`, `owners_variance`, `players_forever`, `players_forever_variance`, `players_2weeks`, `players_2weeks_variance`, `average_forever`, `average_2weeks`) VALUES (".mysql_real_escape_string($the_appid).", ".mysql_real_escape_string($array["owners"]).", ".mysql_real_escape_string($array["owners_variance"]).", ".mysql_real_escape_string($array["players_forever"]).", ".mysql_real_escape_string($array["players_forever_variance"]).", ".mysql_real_escape_string($array["players_2weeks"]).", ".mysql_real_escape_string($array["players_2weeks_variance"]).", ".mysql_real_escape_string($array["average_forever"]).", ".mysql_real_escape_string($array["average_2weeks"]).")";
        $result = mysql_query($sql, $connection);

        // Finally, format the output
        return "{\"owners\": \"".$array["owners"]."\", \"owners_variance\": \"".$array["owners_variance"]."\", \"players_forever\": \"".$array["players_forever"]."\", \"players_forever_variance\": \"".$array["players_forever_variance"]."\", \"players_2weeks\": \"".$array["players_2weeks"]."\", \"players_2weeks_variance\": \"".$array["players_2weeks_variance"]."\", \"average_forever\": \"".$array["average_forever"]."\", \"average_2weeks\": \"".$array["average_2weeks"]."\"}";
    }
}

if (is_numeric($appid)) {
    // checks to see if the value is cached
    $result = mysql_query("SELECT * FROM steamspy WHERE appid='".$appid."' LIMIT 1", $con);
    $num_rows = mysql_num_rows($result);

    // if cached, return the database value
    if ($num_rows > 0) {
        while ($row = mysql_fetch_array($result)) {
            $access_time = strtotime($row['access_time']);
            $current_time = time();

            if ($current_time - $access_time >= 43200) {
                $sql = "DELETE FROM `steamspy` WHERE `appid` = '".mysql_real_escape_string($appid)."'";
                $result = mysql_query($sql, $con);
                $text = GetNewValue($appid, $con);
            } else {
                $text = "{\"owners\": \"".$row["owners"]."\", \"owners_variance\": \"".$row["owners_variance"]."\", \"players_forever\": \"".$row["players_forever"]."\", \"players_forever_variance\": \"".$row["players_forever_variance"]."\", \"players_2weeks\": \"".$row["players_2weeks"]."\", \"players_2weeks_variance\": \"".$row["players_2weeks_variance"]."\", \"average_forever\": \"".$row["average_forever"]."\", \"average_2weeks\": \"".$row["average_2weeks"]."\"}";
            }
        }

    // if not cached or expired, get new value
    } else {
        $text = GetNewValue($appid, $con);
    }

    echo $text;
}
exit;
