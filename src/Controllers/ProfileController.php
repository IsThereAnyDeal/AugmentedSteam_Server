<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Managers\SteamRepManager;
use AugmentedSteam\Server\Model\DataObjects\DBadges;
use AugmentedSteam\Server\Model\User\UserManager;
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

    /**
     * @param array{steamId: int} $params
     * @return array<string, mixed>
     */
    public function getProfile_v2(ServerRequestInterface $request, array $params): array {
        $steamId = (int)$params['steamId'];

        $info = $this->userManager->getProfileInfo($steamId);
        $badges = $this->userManager->getBadges($steamId)
            ->toArray(fn(DBadges $badge) => [
                "title" => $badge->getTitle(),
                "img" => "https://augmentedsteam.com/public/external/badges/{$badge->getImg()}"
            ]);

        return [
            "badges" => $badges,
            "steamrep" => $this->steamRepManager->getReputation($steamId),
            "style" => $info?->getStyle(),
            "bg" => [
                "img" => $info?->getBgImg(),
                "appid" => $info?->getBgAppid(),
            ],
        ];
    }

}
