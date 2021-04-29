<?php
require_once __DIR__ . "/../code/autoloader.php";

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

function GetNewChartValue($the_appid) {
	// Redirect some apps
	switch ($the_appid) {
		case 201270:
			$the_appid = 34330;
			break;
		case 262341:
			$the_appid = 39210;
			break;
		case 262342:
			$the_appid = 39210;
			break;
	}
	$url = "http://steamcharts.com/app/".$the_appid;

	try {
        $filestring = \Core\Load::load($url);
        $find_start  = '<div id="app-heading" class="content">';
        $find_end = '<div id="app-hours-content" class="content">';

        $pos = strpos($filestring, $find_start);
        $pos_end = strpos($filestring, $find_end);

        $substring = substr($filestring, $pos, ($pos_end - $pos));

        preg_match_all("/<span class=\"num\">(.+)<\/span>/", $substring, $matches);
        $now = number_format($matches[1][0]);
        $peak = number_format($matches[1][1]);
        $allpeak = number_format($matches[1][2]);

        \dibi::query("INSERT INTO [steamcharts] ([appid], [one_hour], [one_day], [all_time]) VALUES (%i, %s, %s, %s)",
            $the_appid, $now, $peak, $allpeak);

        return [
            "current" => trim($now),
            "peaktoday" => trim($peak),
            "peakall" => trim($allpeak)
        ];
    } catch (\Exception $e) {
        \Log::channel("exceptions")->info($e->getMessage());
    }
    return [];
}

function GetNewSpyValue($the_appid, \Proxy\LuminatiProxy $proxy) {
	$url = Config::SteamSpyEndpoint.$the_appid;

	try {
		$filestring = \Core\Load::load($url, $proxy->getCurlOptions());
		$a = json_decode($filestring, true);
		if ($a === false || !isset($a['owners'])) { return []; }

		// FIXME update db
		\dibi::query("INSERT INTO [steamspy] %v",
            [
                "appid" => $the_appid,
                "owners" => $a['owners'],
                "owners_variance" => isset($a['owners_variance']) ? $a['owners_variance'] : null,
                "players_forever" => isset($a['players_forever']) ? $a['players_forever'] : null,
                "players_forever_variance" => isset($a['players_forever_variance']) ? $a['players_forever_variance'] : null,
                "players_2weeks" => isset($a['players_2weeks']) ? $a['players_2weeks'] : null,
                "players_2weeks_variance" => isset($a['players_2weeks_variance']) ? $a['players_2weeks_variance'] : null,
                "average_forever" => $a['average_forever'],
                "average_2weeks" => $a['average_2weeks'],
            ]
        );

        \Log::channel("steamspy")->info("Updated $the_appid");
		return [
            "owners" => $a['owners'],
            "average_forever" => $a['average_forever'],
            "average_2weeks" => $a['average_2weeks'],
        ];
	} catch(\Exception $e) {
        \Log::channel("exceptions")->info($e->getMessage());
    }
    return [];
}

function GetNewYouTubeValue($the_appid) {

    $game = \dibi::fetchSingle("SELECT [game] FROM [market_data] WHERE [appid]=%i", $the_appid);

    if (empty($game)) {
        \Log::channel("youtube")->error("Can't find app name for appid $the_appid");
        return null;
    }

    $url = "https://www.googleapis.com/youtube/v3/search?".http_build_query([
        "part" => "snippet",
        "key" => \Config::YouTubeKey,
        "type" => "video",
        "regionCode" => "US",
        "videoEmbeddable" => "true",
        "maxResults" => 10,
    ])."&q=";

    function getDbValue($json) {
        return implode(
            ",",
            array_map(function($item) {
                return $item["id"]["videoId"];
            }, $json["items"]),
        );
    }

	try {
		$reviewJson = json_decode(\Core\Load::load($url.urlencode("$game \"PC\" intitle:Review")), true);
		if ($reviewJson === null) {
            \Log::channel("youtube")->error("Failed to decode review response for $the_appid ($game)");
        } else {
            $reviews = getDbValue($reviewJson);
        }

        $gameplayJson = json_decode(\Core\Load::load($url.urlencode("$game \"PC Gameplay\"")), true);
		if ($gameplayJson === null) {
            \Log::channel("youtube")->error("Failed to decode gameplay response for $the_appid ($game)");
        } else {
            $gameplay = getDbValue($gameplayJson);
        }

        \dibi::query("INSERT INTO [youtube] %v",
            [
                "appid" => $the_appid,
                "reviews" => $reviews,
                "gameplay" => $gameplay,
            ]
        );

        return [
            "reviews" => $reviews,
            "gameplay" => $gameplay,
        ];
	} catch(\Exception $e) {
        \Log::channel("youtube")->error($e->getMessage());
        return null;
    }

}

$data = [];

// Get SteamChart data
{
    $row = \dibi::query("SELECT * FROM [steamcharts] WHERE [appid]=%i", $appid)->fetch();

    // if cached, return the database value
    if (!empty($row)) {
        $access_time = strtotime($row['access_time']);

        if ($current_time - $access_time >= 900) {
            \dibi::query("DELETE FROM [steamcharts] WHERE [appid]=%i", $appid);
            $data['charts']['chart'] = GetNewChartValue($appid);
        } else {
            $data['charts']['chart'] = [
                "current" => trim($row['one_hour']),
                "peaktoday" => trim($row['one_day']),
                "peakall" => trim($row['all_time']),
            ];
        }

    // if not cached or expired, get new value
    } else {
        $data['charts']['chart'] = GetNewChartValue($appid);
    }
}

// Get SteamSpy data
{
    $row = \dibi::query("SELECT * FROM [steamspy] WHERE [appid]=%i", $appid)->fetch();

    if (!empty($row)) {
        $access_time = strtotime($row['access_time']);

        if ($current_time - $access_time >= 86400) {
            \dibi::query("DELETE FROM `steamspy` WHERE [appid]=%i", $appid);
            $data['steamspy'] = GetNewSpyValue($appid, $proxy);
        } else {
            $data['steamspy'] = [
                "owners" => $row['owners'],
                "owners_variance" => $row['owners_variance'],
                "players_forever" => $row['players_forever'],
                "players_forever_variance" => $row['players_forever_variance'],
                "players_2weeks" => $row['players_2weeks'],
                "players_2weeks_variance" => $row['players_2weeks_variance'],
                "average_forever" => $row['average_forever'],
                "average_2weeks" => $row['average_2weeks'],
            ];
        }
    } else {
        $data['steamspy'] = GetNewSpyValue($appid, $proxy);
    }
}

// Get WSGF data
{
    try {
        $filestring = \Core\Load::load(Config::WSGFEndpoint.$appid);
        $xml = simplexml_load_string($filestring);
        if ($xml !== false && !empty($xml->children())) {
            $json = json_decode(json_encode($xml), true); // TODO(tfedor) fugly
            $data['wsgf'] = $json['node'];
        }
    } catch(\Exception $e) {
        \Log::channel("exceptions")->info($e->getMessage());
    }
}

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

	// Initialize variables
    $fr = [
        '30' => 0,
        'fi' => 0,
        'va' => 0,
        'ns' => 0,
    ];
	$gs_y = $gs_n = 0;
	$nvidia = $amd = $intel = $other = 0;
	$nvidia_y = $amd_y = $intel_y = $other_y = 0;
	$less = $hd = $wqhd = $fk = 1;
	$less_y = $hd_y = $wqhd_y = $fk_y = 0;

	// Gather data from the records
	foreach($sql as $row) {
	    if (empty($row['fr'])) { continue; }
	    $fr[$row['fr']]++;

		if ($row["mr"] == "less") {
			$less++;
			if ($row["fs"] == "yes") {
				$less_y++;
			}
		}
		if ($row["mr"] == "hd") {
			$hd++;
			if ($row["fs"] == "yes") {
				$hd_y++;
			}
		}
		if ($row["mr"] == "wqhd") {
			$wqhd++;
			if ($row["fs"] == "yes") {
				$wqhd_y++;
			}
		}
		if ($row["mr"] == "4k") {
			$fk++;
			if ($row["fs"] == "yes") {
				$fk_y++;
			}
		}

		if ($row["gs"] == "yes") { $gs_y++; }
		if ($row["gs"] == "no") { $gs_n++; }

		if ($row["gc"] == "nvidia") { $nvidia++; }
		if ($row["gc"] == "amd") { $amd++; }
		if ($row["gc"] == "intel") { $intel++; }
		if ($row["gc"] == "ns") { $other++; }

		if ($row["gc"] == "nvidia" && $row["pw"] == "yes") { $nvidia_y++; }
		if ($row["gc"] == "amd" && $row["pw"] == "yes") { $amd_y++; }
		if ($row["gc"] == "intel" && $row["pw"] == "yes") { $intel_y++; }
		if ($row["gc"] == "ns" && $row["pw"] == "yes") { $other_y++; }
	}

	// Determine Framerate rendered
	if ($num_rows == 0) { $num_rows = 1; }

	if ($fr['30'] >= $fr['fi'] && $fr['30'] >= $fr['va']) {
	    $data['survey']['fr'] = "30";
	    $data['survey']['frp'] = round(($fr['30'] / $num_rows)*100);
	} elseif ($fr['fi'] > $fr['30'] && $fr['fi'] > $fr['va']) {
        $data['survey']['fr'] = "fi";
        $data['survey']['frp'] = round(($fr['fi'] / $num_rows)*100);
    } elseif ($fr['va'] >= $fr['30'] && $fr['va'] >= $fr['fi']) {
        $data['survey']['fr'] = "va";
        $data['survey']['frp'] = round(($fr['va'] / $num_rows)*100);
	}

	// Determine resolution rendered
	$less_avg = $less_y / $less;
	$hd_avg = $hd_y / $hd;
	$wqhd_avg = $wqhd_y / $wqhd;
	$fk_avg = $fk_y / $fk;

	if ($fk_avg >= $wqhd_avg && $fk_avg >= $hd_avg && $fk_avg >= $less_avg) {
	    $data['survey']['mr'] = "4k";
	} elseif ($wqhd_avg >= $fk_avg && $wqhd_avg >= $hd_avg && $wqhd_avg >= $less_avg) {
        $data['survey']['mr'] = "wqhd";
    } elseif ($hd_avg >= $fk_avg && $hd_avg >= $wqhd_avg && $hd_avg >= $less_avg) {
        $data['survey']['mr'] = "hd";
    } elseif ($less_avg >= $fk_avg && $less_avg >= $wqhd_avg && $less_avg >= $hd_avg) {
        $data['survey']['mr'] = "less";
    } else {
        $data['survey']['mr'] = "less";
	}

	// Determine Game Settings rendered
    $data['survey']['gs'] = ($gs_y >= $gs_n);

	// Determine satisfaction rates rendered
	if ($nvidia > 0) {
	    $data['survey']['nvidia'] = round(($nvidia_y / $nvidia)*100);
	}
	if ($amd > 0) {
        $data['survey']['amd'] = round(($amd_y / $amd)*100);
	}
	if ($intel > 0) {
        $data['survey']['intel'] = round(($intel_y / $intel)*100);
	}
	if ($other > 0) {
	    $data['survey']['other'] = round(($other_y / $other)*100);
	}
}

// Get EXFGLS data
{
    $result = \dibi::query("SELECT * FROM [exfgls] WHERE [appid]=%i", $appid)->fetch();
    $data['exfgls'] = [
        "appid" => $appid,
        "excluded" => !empty($result)
    ];
}

// Get hltb data
{
    $data['hltb'] = [
        "success" => false
    ];
    $result = \dibi::query("SELECT [appid], [hltb_id] FROM [game_links] WHERE [appid]=%i", $appid)->fetch();
    // FIXME this is not updated from anywhere

	if (!empty($result)) {
		$hltb_url = "http://www.howlongtobeat.com/game.php?id=".$result['hltb_id'];
		$hltb_submit = "https://howlongtobeat.com/submit.php?s=add&gid=".$result['hltb_id'];

		try {
            $main_story = "";
            $main_extras = "";
            $comp = "";
		    $filestring = \Core\Load::load($hltb_url);
            if (preg_match("/<h5>Main Story<\/h5>\n(.+)\n/", $filestring, $matches)) {
                $main_story = $matches[1];
            }

            if (preg_match("/<h5>Main \+ Extras<\/h5>\n(.+)\n/", $filestring, $matches)) {
                $main_extras = $matches[1];
            }

            if (preg_match("/<h5>Completionist<\/h5>\n(.+)\n/", $filestring, $matches)) {
                $comp = $matches[1];
            }

            $main_story = strip($main_story);
            $main_extras = strip($main_extras);
            $comp = strip($comp);

            $data['hltb']['success'] = true;
            $data['hltb']['main_story'] = trim($main_story);
            $data['hltb']['main_extras'] = trim($main_extras);
            $data['hltb']['comp'] = trim($comp);
            $data['hltb']['url'] = $hltb_url;
            $data['hltb']['submit_url'] = $hltb_submit;
        } catch(Exception $e) {
		    \Log::channel("exceptions")->info($e->getMessage());
		    // success is false by default
        }
	}
}


try {
    $url = "https://api.isthereanydeal.com/v01/augmentedsteam/info/?".http_build_query([
            "key" => Config::IsThereAnyDealKey,
            "appid" => $appid
        ]);
    $result = \Core\Load::load($url);
    $json = json_decode($result, true);

    if (isset($json['data'])) {
        if (isset($json['data']['metacritic']['userscore'])) {
            $data['data']['userscore'] = $json['data']['metacritic']['userscore']/10; // TODO wrong namespace - doubled "data" key, need to fix in extension
        }

        if (isset($json['data']['opencritic'])) {
            $opencritic = $json['data']['opencritic'];

            $reviews = [];
            foreach($opencritic['reviews'] as $r) {
                $reviews[] = [
                    "date" => $r['publishedDate'],
                    "snippet" => $r['snippet'],
                    "dScore" => $r['displayScore'],
                    "rUrl" => $r['externalUrl'],
                    "author" => $r['author'],
                    "name" => $r['outletName'],
                ];
            }

            $data['oc'] = [
                "url" => $opencritic['url'],
                "score" => $opencritic['score'],
                "award" => $opencritic['award'],
                "reviews" => $reviews,
            ];
        }
    }

} catch(\Exception $e) {
    // ignore exception
}

{
    $result = \dibi::fetch("SELECT * FROM [youtube] WHERE [appid]=%i", $appid);

    if (!empty($result)) {
        $access_time = strtotime($result['access_time']);

        if ($current_time - $access_time >= 60 * 60 * 24 * 7) {
            \dibi::query("DELETE FROM `youtube` WHERE [appid]=%i", $appid);
            $data['youtube'] = GetNewYouTubeValue($appid);
        } else {
            $data['youtube'] = [
                "reviews" => $result['reviews'],
                "gameplay" => $result['gameplay'],
            ];
        }
    } else {
        $data['youtube'] = GetNewYouTubeValue($appid);
    }
}

$response = new \Api\Response();
$response
    ->data($data)
    ->respond();
