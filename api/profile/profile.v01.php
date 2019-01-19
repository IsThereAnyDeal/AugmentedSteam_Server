<?php
require_once __DIR__ . "/../../code/autoloader.php";

\Core\Database::connect();

$endpoint = (new \Api\Endpoint())
    ->params(["profile"]);


if (!is_numeric($endpoint->getParam("profile"))) {
    (new \Api\Response())->fail();
}

$steamId = $endpoint->getParamAsInt("profile");

$current_time = time();

$result = [
    "badges" => [],
    "steamrep" => [],
    "style" => null,
    "bg" => [
        "img" => null,
        "small" => null,
        "appid" => null,
    ],
];

$background_base = "//steamcommunity.com/economy/image/";

$select = \dibi::query("SELECT [link], [title], [img]
                        FROM [supporter_users] as u
                        JOIN [supporter_badges] as b ON [u.badge_id]=[b.id]
                        WHERE [steam_id]=%i
                        ORDER BY [badge_id]", $steamId);

if (!empty($select)) {
    foreach($result as $a) {
        $result['badges'] = [
            "link" => $a['link'],
            "title" => $a['title'],
            "img" => $a['title'],
        ];
    }
}

$result['steamrep'] = (new \SteamRep\SteamRep($steamId))->getRep();

// profile style
$select = \dibi::query("SELECT [profile_style] FROM [profile_style_users] WHERE [steam64]=%i", $steamId)->fetchSingle();
$result['style'] = (empty($select) ? null : $select);

// profile background
$select = \dibi::query("SELECT * FROM [profile_users] WHERE [steam64]=%i", $steamId)->fetch();
if (!empty($select)) {
    $result['bg']['img'] = $select['profile_background_img'];
    $result['bg']['small'] = $select['profile_background_img']."/252fx160f";
    $result['bg']['appid'] = $select['appid'];
}

(new \Api\Response())
    ->data($result)
    ->respond();
