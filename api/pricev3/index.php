<?php
require_once __DIR__."/../../code/autoloader.php";

\Core\Database::connect();

$response = new \Api\Response();
$response->setHeaders();


// Get parameters to build string
$subs = []; $stores = ""; $cc = ""; $voucher = ""; $appid = ""; $bundle = "";
if (isset($_GET['subs'])) {	$subs = explode(",",mysql_real_escape_string($_GET['subs'])); }
if (isset($_GET['stores'])) { $stores = mysql_real_escape_string($_GET['stores']); }
if (isset($_GET['cc'])) { $cc = mysql_real_escape_string($_GET['cc']); }
if (isset($_GET['coupon'])) { $voucher = mysql_real_escape_string($_GET['coupon']); }
if (isset($_GET['appid'])) { $appid = mysql_real_escape_string($_GET['appid']); }
if (isset($_GET['bundle'])) { $bundle = mysql_real_escape_string($_GET['bundleid']); }

$search_string = "";

if ($bundle <> "") {
    $search_string = "bundle/" . $bundle;
} else {
    foreach ($subs as &$searchvalue) {
        if ($searchvalue) {
            $search_string = $search_string."sub/".$searchvalue.",";
        }
    }
}
if ($search_string == "" && $appid) {
    $search_string = "app/" . $appid;
}

$url = "https://api.isthereanydeal.com/v01/game/overview/?shop=steam&ids=".$search_string."&country=".$cc;

if ($stores <> "") {
    $url = $url."&allowed=".$stores;
}

if ($voucher == true) {
    $url = $url."&optional=voucher";
}

$url = $url."&key=".Config::IsThereAnyDealKey;

$filestring = file_get_contents($url);

// Convert formatting
$filestring = str_replace("},\"sub\/", "},\"", $filestring);
$filestring = str_replace("$", "", $filestring);

// Convert JSON results into variables
$array = json_decode($filestring, true);

echo json_encode($array);

