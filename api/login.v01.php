<?php
require_once __DIR__ . "/../code/autoloader.php";

\Core\Database::connect();

$openid = new LightOpenID(Config::SteamLoginOpenIdHost);

if (!$openid->mode) {
    $openid->returnUrl = Config::SteamLoginOpenIdHost . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $openid->identity = "https://steamcommunity.com/openid";
    \Core\Redirect::to($openid->authUrl());
}

$response = new \Api\Response();

if (!$openid->validate()) {
    $response->fail("openid_verification", "Can't verify claimed identifier", 400);
}

preg_match("#id/(\d+)$#", $openid->identity, $matches);

if (!array_key_exists(1, $matches)) {
    $response->fail("steamid_match", "Can't match Steam ID from OP response", 400);
}

$sessionID = openssl_random_pseudo_bytes(16, $strongResult);

if (!$strongResult) {
    $response->fail("weak_crypto", "Can't use cryptographically strong algorithm", 500);
}

$steamID = $matches[1];
$expiry = strtotime("+4 weeks");

\dibi::query("INSERT INTO [session_ids] ([session_id], [steam_id], [expiry]) VALUES (%bin, %s, %dt)", $sessionID, $steamID, $expiry);

setcookie("session_id", bin2hex($sessionID), [
    "expires"   => $expiry,
    "path"      => "/",
    "secure"    => true,
    "httponly"  => true,
]);

\Core\Redirect::to(Config::SteamLoginOpenIdHost . "/connectaugmentedsteam");
