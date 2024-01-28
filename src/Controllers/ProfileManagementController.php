<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\Market\MarketManager;
use AugmentedSteam\Server\Model\User\UserManager;
use AugmentedSteam\Server\OpenId\Session;
use IsThereAnyDeal\Database\DbDriver;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileManagementController extends Controller
{
    private CoreConfig $config;
    private MarketManager $marketManager;
    private UserManager $userManager;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        DbDriver $db,
        CoreConfig $config,
        MarketManager $marketManager,
        UserManager $userManager
    ) {
        parent::__construct($responseFactory, $db);
        $this->config = $config;
        $this->marketManager = $marketManager;
        $this->userManager = $userManager;
    }

    public function getBackgroundsV2(ServerRequestInterface $request): array {
        $appid = (new Param($request, "appid"))->int();

        return $this->marketManager
            ->getBackgrounds($appid);
    }

    public function getGamesV1(ServerRequestInterface $request): array {
        return $this->marketManager
            ->getGamesWithBackgrounds();
    }

    private function authorize(ServerRequestInterface $request, string $selfUrl, string $returnUrl, ?int $profile) {
        $session = new Session($this->db, $this->config->getHost(), $selfUrl);
        if (!$session->isAuthenticated($request, $profile)) {
            if (!$session->isAuthenticationStarted()) {
                return new RedirectResponse($session->getAuthUrl()->toString());
            }

            if (!$session->authenticate()) {
                return new RedirectResponse($returnUrl."#as-failure");
            }
        }

        return (int)$session->getSteamId();
    }

    public function deleteBackgroundV2(ServerRequestInterface $request): RedirectResponse {
        $returnUrl = "https://steamcommunity.com/my/profile";

        $profile = (new Param($request, "profile"))->default(null)->int();

        $authResponse = $this->authorize($request, "/v1/profile/background/edit/delete/", $returnUrl, $profile);
        if ($authResponse instanceof RedirectResponse) {
            return $authResponse;
        } else {
            $steamId = $authResponse;
        }

        $this->userManager
            ->deleteBackground($steamId);

        return new RedirectResponse($returnUrl."#as-success");
    }

    public function saveBackgroundV2(ServerRequestInterface $request): RedirectResponse {
        $returnUrl = "https://steamcommunity.com/my/profile";

        try {
            $appid = (new Param($request, "appid"))->int();
            $img = (new Param($request, "img"))->string();
        } catch(\Exception $e) {
            return new RedirectResponse($returnUrl."#as-error:badrequest");
        }

        if (!$this->marketManager->doesBackgroundExist($appid, $img)) {
            return new RedirectResponse($returnUrl."#as-error:notfound");
        }

        $profile = (new Param($request, "profile"))->default(null)->int();

        $authResponse = $this->authorize($request, "/v1/profile/background/edit/save/?appid=$appid&img=$img", $returnUrl, $profile);
        if ($authResponse instanceof RedirectResponse) {
            return $authResponse;
        } else {
            $steamId = $authResponse;
        }

        $this->userManager
            ->saveBackground($steamId, $appid, $img);

        return new RedirectResponse($returnUrl."#as-success");
    }

    public function deleteStyleV2(ServerRequestInterface $request): RedirectResponse {
        $returnUrl = "https://steamcommunity.com/my/profile";

        $profile = (new Param($request, "profile"))->default(null)->int();

        $authResponse = $this->authorize($request, "/v1/profile/style/edit/delete/", $returnUrl, $profile);
        if ($authResponse instanceof RedirectResponse) {
            return $authResponse;
        } else {
            $steamId = $authResponse;
        }

        $this->userManager
            ->deleteStyle($steamId);

        return new RedirectResponse($returnUrl."#as-success");
    }

    public function saveStyleV2(ServerRequestInterface $request): RedirectResponse {
        $returnUrl = "https://steamcommunity.com/my/profile";

        try {
            $style = (new Param($request, "style"))->string();
        } catch(\Exception $e) {
            return new RedirectResponse($returnUrl."#as-error:badrequest");
        }

        $profile = (new Param($request, "profile"))->default(null)->int();

        $authResponse = $this->authorize($request, "/v1/profile/style/edit/save/?style=$style", $returnUrl, $profile);
        if ($authResponse instanceof RedirectResponse) {
            return $authResponse;
        } else {
            $steamId = $authResponse;
        }

        $this->userManager
            ->saveStyle($steamId, $style);

        return new RedirectResponse($returnUrl."#as-success");
    }

}
