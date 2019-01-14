<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();

	
// Open JSON text
$text = '{ ';

// Select record for passed APPID
$appid = mysql_real_escape_string($_GET['appid'], $con);

$sql = mysql_query("SELECT * FROM `game_survey` WHERE `appid`='".$appid."'");
$num_rows = mysql_num_rows($sql);
if ($num_rows == 0) {
    $text = $text . "\"success\": false,";
} else {
    $text = $text . "\"success\": true, \"responses\": ".$num_rows.",";
}

// Initialize variables
$fr_30 = $fr_fi = $fr_va = 0;
$gs_y = $gs_n = 0;
$nvidia = $amd = $intel = $other = 0;
$nvidia_y = $amd_y = $intel_y = $other_y = 0;
$less = $hd = $wqhd = $fk = 1;
$less_y = $hd_y = $wqhd_y = $fk_y = 0;

// Gather data from the records
while($row = mysql_fetch_array($sql)) {
    switch ($row["fr"]) {
        case "30":
            $fr_30 = $fr_30 + 1;
            break;
        case "fi":
            $fr_fi = $fr_fi + 1;
            break;
        case "va":
            $fr_va = $fr_va + 1;
            break;
    }

    if ($row["mr"] == "less") {
        $less = $less + 1;
        if ($row["fs"] == "yes") {
            $less_y = $less_y + 1;
        }
    }
    if ($row["mr"] == "hd") {
        $hd = $hd + 1;
        if ($row["fs"] == "yes") {
            $hd_y = $hd_y + 1;
        }
    }
    if ($row["mr"] == "wqhd") {
        $wqhd = $wqhd + 1;
        if ($row["fs"] == "yes") {
            $wqhd_y = $wqhd_y + 1;
        }
    }
    if ($row["mr"] == "4k") {
        $fk = $fk + 1;
        if ($row["fs"] == "yes") {
            $fk_y = $fk_y + 1;
        }
    }

    if ($row["gs"] == "yes") { $gs_y = $gs_y + 1; }
    if ($row["gs"] == "no") { $gs_n = $gs_n + 1; }

    if ($row["gc"] == "nvidia") { $nvidia = $nvidia + 1; }
    if ($row["gc"] == "amd") { $amd = $amd + 1; }
    if ($row["gc"] == "intel") { $intel = $intel + 1; }
    if ($row["gc"] == "ns") { $other = $other + 1; }

    if ($row["gc"] == "nvidia" && $row["pw"] == "yes") { $nvidia_y = $nvidia_y + 1; }
    if ($row["gc"] == "amd" && $row["pw"] == "yes") { $amd_y = $amd_y + 1; }
    if ($row["gc"] == "intel" && $row["pw"] == "yes") { $intel_y = $intel_y + 1; }
    if ($row["gc"] == "ns" && $row["pw"] == "yes") { $other_y = $other_y + 1; }
}

// Determine Framerate rendered
if ($fr_30 >= $fr_fi && $fr_30 >= $fr_va) {
    $text = $text . "\"fr\": \"30\",";
    $text = $text . "\"frp\": ".round(($fr_30 / $num_rows)*100).",";
} else {
    if ($fr_fi > $fr_30i && $fr_fi > $fr_va) {
        $text = $text . "\"fr\": \"fi\",";
        $text = $text . "\"frp\": ".round(($fr_fi / $num_rows)*100).",";
    } else {
        if ($fr_va >= $fr_30 && $fr_va >= $fr_fi) {
            $text = $text . "\"fr\": \"va\",";
            $text = $text . "\"frp\": ".round(($fr_va / $num_rows)*100).",";
        }
    }
}

// Determine resolution rendered
$less_avg = $less_y / $less;
$hd_avg = $hd_y / $hd;
$wqhd_avg = $wqhd_y / $wqhd;
$fk_avg = $fk_y / $fk;

if ($fk_avg > $wqhd_avg && $fk_avg > $hd_avg && $fk_avg > $less_avg) {
    $text = $text . "\"mr\": \"4k\",";
} else {
    if ($wqhd_avg > $fk_avg && $wqhd_avg > $hd_avg && $wqhd_avg > $less_avg) {
        $text = $text . "\"mr\": \"wqhd\",";
    } else {
        if ($hd_avg > $fk_avg && $hd_avg > $wqhd_avg && $hd_avg > $less_avg) {
            $text = $text . "\"mr\": \"hd\",";
        } else {
            if ($less_avg >= $fk_avg && $less_avg >= wqhd_avg && $less_avg >= $hd_avg) {
                $text = $text . "\"mr\": \"less\",";
            } else {
                $text = $text . "\"mr\": \"less\",";
            }
        }
    }
}

// Determine Game Settings rendered
if ($gs_y >= $gs_n) {
    $text = $text . "\"gs\": true,";
} else {
    $text = $text . "\"gs\": false,";
}

// Determine satisfaction rates rendered
if ($nvidia > 0) {
    $text = $text . "\"nvidia\": ".round(($nvidia_y / $nvidia)*100).",";
}
if ($amd > 0) {
    $text = $text . "\"amd\": ".round(($amd_y / $amd)*100).",";
}
if ($intel > 0) {
    $text = $text . "\"intel\": ".round(($intel_y / $intel)*100).",";
}
if ($other > 0) {
    $text = $text . "\"other\": ".round(($other_y / $other)*100).",";
}

// Close JSON text
$text = substr($text, 0, -1);
$text = $text . "}";

// Echo JSON text
echo $text;
