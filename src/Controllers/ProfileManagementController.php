<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\Market\MarketManager;
use AugmentedSteam\Server\Model\User\UserManager;
use AugmentedSteam\Server\OpenId\OpenId;
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

    public function getBackgroundV1(ServerRequestInterface $request): array {
        $appid = (new Param($request, "appid"))->int();

        $backgrounds = $this->marketManager
            ->getBackgrounds($appid);

        $result = [];
        foreach($backgrounds as $o) {
            $result[] = [
                $o->getImg(),
                preg_replace("#\s*\(Profile Background\)#", "", $o->getName()),
            ];
        }

        return $result;
    }

    public function getGamesV1(ServerRequestInterface $request): array {

        return $this->marketManager
            ->getGames();
    }

    public function deleteBackgroundV1(ServerRequestInterface $request): RedirectResponse {
        $returnUrl = "https://steamcommunity.com/my/profile";

        $openId = new OpenId($this->config->getHost(), "/v1/profile/background/edit/delete/");
        if (!$openId->isAuthenticationStarted()) {
            return new RedirectResponse($openId->getAuthUrl()->toString());
        }

        if (!$openId->authenticate()) {
            return new RedirectResponse($returnUrl."#as-failure");
        }

        $this->userManager
            ->deleteBackground((int)$openId->getSteamId());

        return new RedirectResponse($returnUrl."#as-success");
    }

    public function saveBackgroundV1(ServerRequestInterface $request): RedirectResponse {
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

        $openId = new OpenId($this->config->getHost(), "/v1/profile/background/edit/save/?appid=$appid&img=$img");
        if (!$openId->isAuthenticationStarted()) {
            return new RedirectResponse($openId->getAuthUrl()->toString());
        }

        if (!$openId->authenticate()) {
            return new RedirectResponse($returnUrl."#as-failure");
        }

        $this->userManager
            ->saveBackground((int)$openId->getSteamId(), $appid, $img);

        return new RedirectResponse($returnUrl."#as-success");
    }

    public function deleteStyleV1(ServerRequestInterface $request): RedirectResponse {
        $returnUrl = "https://steamcommunity.com/my/profile";

        $openId = new OpenId($this->config->getHost(), "/v1/profile/style/edit/delete/");
        if (!$openId->isAuthenticationStarted()) {
            return new RedirectResponse($openId->getAuthUrl()->toString());
        }

        if (!$openId->authenticate()) {
            return new RedirectResponse($returnUrl."#as-failure");
        }

        $this->userManager
            ->deleteStyle((int)$openId->getSteamId());

        return new RedirectResponse($returnUrl."#as-success");
    }

    public function saveStyleV1(ServerRequestInterface $request): RedirectResponse {
        $returnUrl = "https://steamcommunity.com/my/profile";

        try {
            $style = (new Param($request, "style"))->string();
        } catch(\Exception $e) {
            return new RedirectResponse($returnUrl."#as-error:badrequest");
        }

        $openId = new OpenId($this->config->getHost(), "/v1/profile/style/edit/save/?style=$style");
        if (!$openId->isAuthenticationStarted()) {
            return new RedirectResponse($openId->getAuthUrl()->toString());
        }

        if (!$openId->authenticate()) {
            return new RedirectResponse($returnUrl."#as-failure");
        }

        $this->userManager
            ->saveStyle((int)$openId->getSteamId(), $style);

        return new RedirectResponse($returnUrl."#as-success");
    }

}
