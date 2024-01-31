<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Managers\SteamPeekManager;
use AugmentedSteam\Server\Data\Objects\SteamPeekGame;
use AugmentedSteam\Server\Http\BoolParam;
use AugmentedSteam\Server\Http\IntParam;
use Psr\Http\Message\ServerRequestInterface;

class SimilarController extends Controller {

    public function __construct(
        private readonly SteamPeekManager $steamPeekManager
    ) {}

    public function getSimilar_v2(ServerRequestInterface $request, array $params): array {
        $appid = intval($params['appid']);
        $count = (new IntParam($request, "count", 5))->value();
        $shuffle = (new BoolParam($request, "shuffle", false))->value();

        return array_map(
            fn(SteamPeekGame $game) => [
                "title" => $game->title,
                "appid" => $game->appid,
                "sprating" => $game->rating,
                "score" => $game->score
            ],
            $this->steamPeekManager->getSimilar($appid, $count, $shuffle)
        );
    }
}
