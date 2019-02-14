<?php
require_once __DIR__ . "/../../../../code/autoloader.php";

\Core\Database::connect();

$returnUrl = "https://steamcommunity.com/my/profile";

if (!empty($_GET['style'])) { $style = $_GET['style']; }

if (empty($style)) {
    \Core\Redirect::to($returnUrl."#as-error:badrequest");
}

$openid = new LightOpenID(Config::SteamLoginOpenIdHost);
$openid->returnUrl = "https://".Config::SteamLoginOpenIdHost."/v01/profile/style/edit/save/?style=$style";

if(!$openid->mode) {
    $openid->identity = "https://steamcommunity.com/openid";
    \Core\Redirect::to($openid->authUrl());
} elseif($openid->validate()) {
    $matches = [];
    preg_match("#id/(\d+)$#", $openid->identity, $matches);
    $steamID = $matches[1];

    \dibi::query("INSERT INTO [profile_style_users] ([steam64], [profile_style]) VALUES (%i, %s)
                  ON DUPLICATE KEY UPDATE [profile_style]=VALUES([profile_style])", $steamID, $style);
    \Core\Redirect::to("https://steamcommunity.com/my/profile#as-success");
}
