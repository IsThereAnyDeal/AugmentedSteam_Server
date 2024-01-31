<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Http\IntParam;
use AugmentedSteam\Server\Http\StringParam;
use AugmentedSteam\Server\Model\Market\MarketManager;
use AugmentedSteam\Server\Model\User\UserManager;
use AugmentedSteam\Server\OpenId\Session;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ProfileManagementController extends Controller
{
    private const string ReturnUrl = "https://steamcommunity.com/my/profile";

    private const string Error_BadRequest = "#as-error:badrequest";
    private const string Error_NotFound = "#as-error:notfound";
    private const string Success = "#as-success";
    private const string Failure = "#as-failure";

    public function __construct(
        private readonly Session $session,
        private readonly MarketManager $marketManager,
        private readonly UserManager $userManager
    ) {
    }

    /**
     * @return list<array{string, string}>
     */
    public function getBackgrounds_v2(ServerRequestInterface $request): array {
        $appid = (new IntParam($request, "appid"))->value();

        return $this->marketManager
            ->getBackgrounds($appid);
    }

    /**
     * @return list<array{int, string}>
     */
    public function getGames_v1(ServerRequestInterface $request): array {
        return $this->marketManager
            ->getGamesWithBackgrounds();
    }

    public function deleteBackground_v2(ServerRequestInterface $request): RedirectResponse {
        $profile = (new IntParam($request, "profile", default: null, nullable: true))->value();

        $authResponse = $this->session->authorize(
            $request,
            "/profile/background/delete/v2",
            self::ReturnUrl.self::Failure,
            $profile
        );
        if ($authResponse instanceof RedirectResponse) {
            return $authResponse;
        }

        $steamId = $authResponse;
        $this->userManager
            ->deleteBackground($steamId);

        return new RedirectResponse(self::ReturnUrl.self::Success);
    }

    public function saveBackground_v2(ServerRequestInterface $request): RedirectResponse {
        try {
            $appid = (new IntParam($request, "appid"))->value();
            $img = (new StringParam($request, "img"))->value();
            $profile = (new IntParam($request, "profile", default: null, nullable: true))->value();
        } catch(Throwable) {
            return new RedirectResponse(self::ReturnUrl.self::Error_BadRequest);
        }

        if (!$this->marketManager->doesBackgroundExist($appid, $img)) {
            return new RedirectResponse(self::ReturnUrl.self::Error_NotFound);
        }

        $authResponse = $this->session->authorize(
            $request,
            "/profile/background/save/v2?appid=$appid&img=$img",
            self::ReturnUrl.self::Failure,
            $profile
        );
        if ($authResponse instanceof RedirectResponse) {
            return $authResponse;
        }

        $steamId = $authResponse;
        $this->userManager
            ->saveBackground($steamId, $appid, $img);

        return new RedirectResponse(self::ReturnUrl.self::Success);
    }

    public function deleteStyle_v2(ServerRequestInterface $request): RedirectResponse {
        $profile = (new IntParam($request, "profile", default: null, nullable: true))->value();

        $authResponse = $this->session->authorize(
            $request,
            "/profile/style/delete/v2",
            self::ReturnUrl.self::Failure,
            $profile
        );
        if ($authResponse instanceof RedirectResponse) {
            return $authResponse;
        }

        $steamId = $authResponse;
        $this->userManager
            ->deleteStyle($steamId);

        return new RedirectResponse(self::ReturnUrl.self::Success);
    }

    public function saveStyle_v2(ServerRequestInterface $request): RedirectResponse {
        try {
            $style = (new StringParam($request, "style"))->value();
            $profile = (new IntParam($request, "profile", default: null, nullable: true))->value();
        } catch(Throwable) {
            return new RedirectResponse(self::ReturnUrl.self::Error_BadRequest);
        }

        $authResponse = $this->session->authorize(
            $request,
            "/profile/style/save/v2?style=$style",
            self::ReturnUrl.self::Failure,
            $profile
        );
        if ($authResponse instanceof RedirectResponse) {
            return $authResponse;
        }

        $steamId = $authResponse;
        $this->userManager
            ->saveStyle($steamId, $style);

        return new RedirectResponse(self::ReturnUrl.self::Success);
    }
}
