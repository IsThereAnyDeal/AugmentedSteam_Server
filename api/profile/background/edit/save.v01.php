<?php
require_once __DIR__ . "/../../../../code/autoloader.php";

\Core\Database::connect();

$returnUrl = "https://steamcommunity.com/my/profile";

if (!empty($_GET['appid'])) { $appid = $_GET['appid']; }
if (!empty($_GET['img']))   { $img = $_GET['img']; }

if (empty($appid) || empty($img)) {
    \Core\Redirect::to($returnUrl."#as-error:badrequest");
}

$select = \dibi::query("SELECT 1
                        FROM [market_data]
                        WHERE [appid]=%i AND [img]=%s AND [type]='background'", $appid, $img)->fetchSingle();
if (empty($select)) {
    \Core\Redirect::to($returnUrl."#as-error:notfound");
}

$openid = new LightOpenID(Config::SteamLoginOpenIdHost);
$openid->returnUrl = "https://".Config::SteamLoginOpenIdHost."/v01/profile/background/edit/save/?appid=$appid&img=$img";

if(!$openid->mode) {
    $openid->identity = "https://steamcommunity.com/openid";
    \Core\Redirect::to($openid->authUrl());
} elseif($openid->validate()) {
    $matches = [];
    preg_match("#id/(\d+)$#", $openid->identity, $matches);
    $steamID = $matches[1];

    \dibi::query("INSERT INTO [profile_users] ([steam64], [profile_background_img], [appid]) VALUES (%s, %s, %i)
                  ON DUPLICATE KEY UPDATE
                    [profile_background_img]=VALUES([profile_background_img]),
                    [appid]=VALUES([appid])", $steamID, $img, $appid);
    \Core\Redirect::to("https://steamcommunity.com/my/profile#as-success");
}
