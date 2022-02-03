<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\User\UserManager;
use AugmentedSteam\Server\SteamRep\SteamRepManager;
use IsThereAnyDeal\Database\DbDriver;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileController extends Controller
{
    private UserManager $userManager;
    private SteamRepManager $steamRepManager;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        DbDriver $db,
        UserManager $userManager,
        SteamRepManager $steamRepManager
    ) {
        parent::__construct($responseFactory, $db);
        $this->userManager = $userManager;
        $this->steamRepManager = $steamRepManager;
    }

    public function getProfileV1(ServerRequestInterface $request): array {
        $steamId = (new Param($request, "profile"))->int();

        $result = [
            "badges" => [],
            "steamrep" => [],
            "style" => null,
            "bg" => [
                "img" => null,
                "appid" => null,
            ],
        ];

        $result['steamrep'] = $this->steamRepManager->getRep($steamId);

        $badges = $this->userManager->getBadges($steamId);
        foreach($badges as $badge) {
            $result['badges'][] = [
                "link" => "", // @deprecated
                "title" => $badge->getTitle(),
                "img" => "https://augmentedsteam.com/img/badges/{$badge->getImg()}"
            ];
        }

        $info = $this->userManager->getProfileInfo($steamId);
        if (!is_null($info)) {
            $result['style'] = $info->getStyle();
            $result['bg']['img'] = $info->getBgImg();
            $result['bg']['appid'] = $info->getBgAppid();
        }

        return $result;
    }

}
