<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\SteamPeek\SteamPeekManager;
use IsThereAnyDeal\Database\DbDriver;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class SimilarController extends Controller {

    private SteamPeekManager $steamPeekManager;

    public function __construct(ResponseFactoryInterface $responseFactory, DbDriver $db, SteamPeekManager $steamPeekManager) {
        parent::__construct($responseFactory, $db);
        $this->steamPeekManager = $steamPeekManager;
    }

    public function getSimilarV1(ServerRequestInterface $request): array {
        $appid = (new Param($request, "appid"))->int();
        $count = (new Param($request, "count"))->default(5)->int();
        $shuffle = (new Param($request, "shuffle"))->default(false)->bool();

        $games = $this->steamPeekManager->getSimilar($appid, $count, $shuffle);
        $data = [];
        foreach($games as $game) {
            $data[] = [
                "title" => $game->getTitle(),
                "appid" => $game->getAppid(),
                "sprating" => $game->getSPRating(),
                "score" => $game->getScore()
            ];
        }
        return $data;
    }
}
