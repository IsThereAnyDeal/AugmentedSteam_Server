<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$endpoint = new \Api\Endpoint();
$endpoint->params(["appid"], [
    "r_all" => 0,
    "r_pos" => 0,
    "r_stm" => 0,
    "mcurl" => "",
    "oc" => ""
]);

Log::channel("api", true)->info("storepagedata");

$_response = new \Api\Response();

if (!is_numeric($endpoint->getParam("appid"))) {
    $_response->fail();
}
$appid = $endpoint->getParamAsInt("appid");

$current_time = time();

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

function GetNewSpyValue($the_appid) {
	$url = Config::SteamSpyEndpoint.$the_appid;

	try {
		$filestring = \Core\Load::load($url);
		$a = json_decode($filestring, true);

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

		return [
            "owners" => $a['owners'],
            "owners_variance" => isset($a['owners_variance']) ? $a['owners_variance'] : null,
            "players_forever" => isset($a['players_forever']) ? $a['players_forever'] : null,
            "players_forever_variance" => isset($a['players_forever_variance']) ? $a['players_forever_variance'] : null,
            "players_2weeks" => isset($a['players_2weeks']) ? $a['players_2weeks'] : null,
            "players_2weeks_variance" => isset($a['players_2weeks_variance']) ? $a['players_2weeks_variance'] : null,
            "average_forever" => $a['average_forever'],
            "average_2weeks" => $a['average_2weeks'],
        ];
	} catch(\Exception $e) {
        \Log::channel("exceptions")->info($e->getMessage());
    }
    return [];
}

function GetNewMCValue($url) {
	try {
		$filestring = \Core\Load::load($url);
		preg_match("/metascore_w user(.+)\">(.+)<\/div>/", $filestring, $matches);
		$the_value = $matches[2];

		\dibi::query("INSERT INTO [metacritic] ([mcurl], [score]) VALUES (%s, %f)", $url, $the_value);
		return $the_value;
	} catch(\Exception $e) {
        \Log::channel("exceptions")->info($e->getMessage());
    }
    return null;
}

function GetNewOCValue($the_appid) {
	$url = Config::OpenCriticEndpoint.$the_appid."&key=".Config::OpenCriticKey;

	try {
		$filestring = \Core\Load::load($url);
		if ($filestring) {
			$array = json_decode($filestring, true);

			// Strip stuff from JSON we don't need
			if (isset($array["reviews"])) { unset($array["reviews"]); }
			if (isset($array["id"])) { unset($array["id"]); }
			if (isset($array["sku_id"])) { unset($array["sku_id"]); }
			if (isset($array["name"])) { unset($array["name"]); }

			// Strip stuff from topReviews we don't need
			foreach ($array["topReviews"] as &$value) {
				if ($value["ScoreFormat"]) { unset ($value["ScoreFormat"]); }
				if ($value["OutletId"]) { unset ($value["OutletId"]); }
				if ($value["platform"]) { unset ($value["platform"]); }
				if ($value["language"]) { unset ($value["language"]); }
				unset ($value["isQuoteManual"]);
			}
			unset($value);

			$oc_jason = json_encode($array);
			$oc_jason = str_replace("\/", "/", $oc_jason);
			$oc_jason = str_replace("\u00a0", " ", $oc_jason);
			$oc_jason = str_replace("\u2013", "-", $oc_jason);

			$oc_jason = str_replace("openCriticScore", "score", $oc_jason);
			$oc_jason = str_replace("openCriticUrl", "url", $oc_jason);
			$oc_jason = str_replace("topReviews", "reviews", $oc_jason);
			$oc_jason = str_replace("publishedDate", "date", $oc_jason);
			$oc_jason = str_replace("externalUrl", "rURL", $oc_jason);
			$oc_jason = str_replace("outletName", "name", $oc_jason);
			$oc_jason = str_replace("displayScore", "dScore", $oc_jason);
			$oc_jason = str_replace("reviewCount", "count", $oc_jason);

			\dibi::query("INSERT INTO [opencritic] ([appid], [json]) VALUES (%i, %s)", $the_appid, $oc_jason);
			return json_decode($oc_jason, true);
		} else {
			return [];
		}
	} catch(\Exception $e) {
        \Log::channel("exceptions")->info($e->getMessage());
    }
    return [];
}

// store steam reviews
{
    $reviewsAll = $endpoint->getParam("r_all");
    $reviewsPositive = $endpoint->getParam("r_pos");
    $reviewsPurchasedOnSteam = $endpoint->getParam("r_stm");

    if (is_numeric($reviewsAll) && is_numeric($reviewsPositive) && is_numeric($reviewsPurchasedOnSteam)) {
        $select = \dibi::query("SELECT * FROM [steam_reviews] WHERE [appid]=%i", $appid)->fetch();

        $params = [
            "appid" => $appid,
            "total" => $reviewsAll,
            "pos" => $reviewsPositive,
            "stm" => $reviewsPurchasedOnSteam,
        ];

        if ($select !== false) {
            $access_time = strtotime($select['update_time']);

            if ($current_time - $access_time >= 43200) {
                if ($reviewsAll > $select['total'] || $reviewsPositive >= $select['pos'] || $reviewsPurchasedOnSteam >= $select['stm'] || $current_time - $access_time >= 259200) {
                    \dibi::query("INSERT INTO [steam_reviews] %v
                              ON DUPLICATE KEY UPDATE [total]=VALUES([total]), [pos]=VALUES([pos]), [stm]=VALUES([stm])", $params);
                }
            }
        } else {
            \dibi::query("INSERT INTO [steam_reviews] %v", $params);
        }
    }
}

$data = [];

// Get SteamChart data
{
    $row = \dibi::query("SELECT * FROM [steamcharts] WHERE [appid]=%i", $appid)->fetch();

    // if cached, return the database value
    if (!empty($row)) {
        $access_time = strtotime($row['access_time']);

        if ($current_time - $access_time >= 3600) {
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

// Get OpenCritic data
{
	if (!empty($endpoint->getParam("oc"))) {
	    $row = \dibi::query("SELECT * FROM [opencritic] WHERE [appid]=%i", $appid)->fetch();

	    if (!empty($row)) {
	        $access_time = strtotime($row['access_time']);
            if ($current_time - $access_time >= 3600) {

                \dibi::query("DELETE FROM [opencritic] WHERE [appid]=%i", $appid);
                $ocData = GetNewOCValue($appid);
            } else {
                $ocData = json_decode($row['json'], true);
            }

            if (!empty($ocData)) {
                $data['oc'] = $ocData ;
            }
        } else {
	        $data['oc'] = GetNewOCValue($appid);
        }
	}
}

// Get SteamSpy data
{
    $row = \dibi::query("SELECT * FROM [steamspy] WHERE [appid]=%i", $appid)->fetch();

    if (!empty($row)) {
        $access_time = strtotime($row['access_time']);

        if ($current_time - $access_time >= 43200) {
            \dibi::query("DELETE FROM `steamspy` WHERE [appid]=%i", $appid);
            $data['steamspy'] = GetNewSpyValue($appid);
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
        $data['steamspy'] = GetNewSpyValue($appid);
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
        'va' => 0
    ];
	$gs_y = $gs_n = 0;
	$nvidia = $amd = $intel = $other = 0;
	$nvidia_y = $amd_y = $intel_y = $other_y = 0;
	$less = $hd = $wqhd = $fk = 1;
	$less_y = $hd_y = $wqhd_y = $fk_y = 0;

	// Gather data from the records
	foreach($sql as $row) {
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

            $data['htlb']['success'] = true;
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

// Get optional Metacritic data
{
    if (isset($mcurl)) {
        if (substr($mcurl, 0, 26) == "http://www.metacritic.com/") {
            // checks to see if the value is cached
            $row = \dibi::query("SELECT * FROM [metacritic] WHERE [mcurl]=%s")->fetch();

            // if cached, return the database value
            if (!empty($row)) {
                $access_time = strtotime($row['access_time']);

                if ($current_time - $access_time >= 28800) {
                    \dibi::query("DELETE FROM [metacritic] WHERE [mcurl]=%s", $mcurl);
                    $text = GetNewMCValue($mcurl);
                    if ($text == "") { $text = "0"; }
                    $data['metacritic']['userscore'] = $text;
                } else {
                    $data['metacritic']['userscore'] = $row['score'];
                }

            // if not cached or expired, get new value
            } else {
                $text = GetNewMCValue($mcurl);
                if ($text == "") { $text = "0"; }
                $data['metacritic']['userscore'] = $text;
            }
        }
    }
}

$response = new \Api\Response();
$response
    ->data($data)
    ->respond();

