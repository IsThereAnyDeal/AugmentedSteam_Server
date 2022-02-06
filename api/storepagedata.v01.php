<?php
require_once __DIR__ . "/../code/autoloader.php";
require_once __DIR__ . "/../code/Survey/ValidValues.php";

\Core\Database::connect();

$endpoint = new \Api\Endpoint();
$endpoint->params(["appid"], [
    "oc" => ""
]);

Log::channel("api", true)->info("storepagedata");

$_response = new \Api\Response();

if (!is_numeric($endpoint->getParam("appid"))) {
    $_response->fail();
}
$appid = $endpoint->getParamAsInt("appid");

$current_time = time();

$proxy = new \Proxy\LuminatiProxy(Config::ProxyUser, Config::ProxyPassword, Config::ProxyZone);

// sanitation function
function strip($string) {
	$string = str_replace("&#189;", "", $string);
	$string = str_replace("<div class='time_100' >", "", $string);
	$string = str_replace("<div class='time_90' >", "", $string);
	$string = str_replace("<div class='time_80' >", "", $string);
	$string = str_replace("<div class='time_70' >", "", $string);
	$string = str_replace("<div class='time_60' >", "", $string);
	$string = str_replace("<div class='time_50' >", "", $string);
	$string = str_replace("<div class='time_40' >", "", $string);
	$string = str_replace("<div class='time_30' >", "", $string);
	$string = str_replace("<div class='time_20' >", "", $string);
	$string = str_replace("<div class='time_10' >", "", $string);
	$string = str_replace("<div class='time_00' >", "", $string);

	$string = str_replace("<div class='time_100' >", "", $string);
	$string = str_replace("<div class='time_90' >", "", $string);
	$string = str_replace("<div class='time_80' >", "", $string);
	$string = str_replace("<div class='time_70' >", "", $string);
	$string = str_replace("<div class='time_60' >", "", $string);
	$string = str_replace("<div class='time_50' >", "", $string);
	$string = str_replace("<div class='time_40' >", "", $string);
	$string = str_replace("<div class='time_30' >", "", $string);
	$string = str_replace("<div class='time_20' >", "", $string);
	$string = str_replace("<div class='time_10' >", "", $string);
	$string = str_replace("<div class='time_00' >", "", $string);

	$string = str_replace("</div>", "", $string);
	$string = str_replace("<div>", "", $string);

	return $string;
}

$data = [];

// Get Survey data
{
    $sql = \dibi::query("SELECT * FROM [game_survey] WHERE [appid]=%i", $appid);

    $num_rows = count($sql);

    if ($num_rows == 0) {
        $data['survey']['success'] = false;
    } else {
        $data['survey']['success'] = true;
        $data['survey']['responses'] = count($sql);
    }

    $keys = [];

    foreach (SURVEY_VALID_VALUES as $key => $values) {
        $keys[] = $key;
        $values[] = "total";

        $$key = [];
        // array_fill_keys weirdly converts false to "" and not 0, so do it manually
        foreach ($values as $value) {
            $$key[$value] = 0;
        }
    }

    function countEntry($row, $key, &$var) {
        $value = $row[$key];

        if (is_null($value)) { return; }

        $var[$value]++;
        $var["total"]++;
    }

	foreach ($sql as $row) {
        foreach ($keys as $key) {
            countEntry($row, $key, $$key);
        }
	}

    function calculateMostVoted($counts, $name, &$survey) {
        $totalCount = $counts["total"];
        unset($counts["total"]);

        $maxCount = max($counts);

        // This may be ambigous if there are multiple values with the same count
        $mostVoted = array_search($maxCount, $counts);

        $survey[$name] = $mostVoted;
        $survey["${name}p"] = round(($maxCount / ($totalCount === 0 ? 1 : $totalCount)) * 100);
    }

    foreach ($keys as $key) {
        calculateMostVoted($$key, $key, $data["survey"]);
    }
}

$response = new \Api\Response();
$response
    ->data($data)
    ->respond();
