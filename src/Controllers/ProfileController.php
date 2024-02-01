<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Managers\SteamRepManager;
use AugmentedSteam\Server\Data\Managers\UserManager;
use AugmentedSteam\Server\Database\DBadges;
use Psr\Http\Message\ServerRequestInterface;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly SteamRepManager $steamRepManager
    ) {}

    /**
     * @param array{steamId: int} $params
     * @return array<string, mixed>
     */
    public function profile_v2(ServerRequestInterface $request, array $params): array {
        $steamId = (int)$params['steamId'];

        $info = $this->userManager->getProfileInfo($steamId);
        $badges = $this->userManager->getBadges($steamId)
            ->toArray(fn(DBadges $badge) => [
                "title" => $badge->getTitle(),
                "img" => $badge->getImg()
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
