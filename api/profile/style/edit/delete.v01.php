<?php
require_once __DIR__ . "/../../../../code/autoloader.php";

\Core\Database::connect();

$returnUrl = "https://steamcommunity.com/my/profile";

$openid = new LightOpenID(Config::SteamLoginOpenIdHost);
$openid->returnUrl = "https://".Config::SteamLoginOpenIdHost."/v01/profile/style/edit/delete/";

if(!$openid->mode) {
    $openid->identity = "https://steamcommunity.com/openid";
    \Core\Redirect::to($openid->authUrl());
} elseif($openid->validate()) {
    $matches = [];
    preg_match("#id/(\d+)$#", $openid->identity, $matches);
    $steamID = $matches[1];

    \dibi::query("DELETE FROM [profile_style_users] WHERE [steam64]=%i", $steamID);
    \Core\Redirect::to("https://steamcommunity.com/my/profile#as-success");
}
