<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\DataObjects\DSurvey;
use AugmentedSteam\Server\Model\Survey\EGraphicsSettings;
use AugmentedSteam\Server\Model\Survey\SurveyManager;
use AugmentedSteam\Server\OpenId\Session;
use IsThereAnyDeal\Database\DbDriver;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class SurveyController extends Controller
{
    private SurveyManager $surveyManager;
    private CoreConfig $config;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        DbDriver $db,
        CoreConfig $config,
        SurveyManager $surveyManager
    ) {
        parent::__construct($responseFactory, $db);
        $this->surveyManager = $surveyManager;
        $this->config = $config;
    }

    public function getSubmitV1(ServerRequestInterface $request, array $params): RedirectResponse {
        $appid = (int)$params['appid'];
        $returnUrl = "https://store.steampowered.com/app/{$appid}/";

        $steamId = (new Param($request, "profile"))->default(null)->int();
        $framerate = (new Param($request, "framerate"))->default(null)->string();
        $optimized = (new Param($request, "optimized"))->default(null)->string();
        $lag = (new Param($request, "lag"))->default(null)->string();
        $graphicsSettings = (new Param($request, "graphics_settings"))->default(null)->string();
        $bgSoundMute = (new Param($request, "bg_sound"))->default(null)->string();
        $controls = (new Param($request, "good_controls"))->default(null)->string();

        $params = http_build_query([
            "appid" => $appid,
            "profile" => $steamId,
            "framerate" => $framerate,
            "optimized" => $optimized,
            "lag" => $lag,
            "graphics_settings" => $graphicsSettings,
            "bg_sound" => $bgSoundMute,
            "good_controls" => $controls
        ]);

        $session = new Session($this->db, $this->config->getHost(), "/v1/survey/submit/?$params");
        if (!$session->isAuthenticated($request, $steamId)) {
            if (!$session->isAuthenticationStarted()) {
                return new RedirectResponse($session->getAuthUrl()->toString());
            }

            if (!$session->authenticate()) {
                return new RedirectResponse($returnUrl."#as-failure");
            }
        }

        $this->surveyManager->submit(
            (new DSurvey())
                ->setAppid($appid)
                ->setSteamId((int)$session->getSteamId())
                ->setFramerate(["th" => 30, "sx" => 60, "va" => 0][$framerate] ?? null)
                ->setOptimized(is_null($optimized) ? null : (int)($optimized == "yes"))
                ->setLag(is_null($lag) ? null : (int)($lag == "yes"))
                ->setGraphicsSettings([
                        "no" => EGraphicsSettings::None,
                        "bs" => EGraphicsSettings::Basic,
                        "gr" => EGraphicsSettings::Granular
                    ][$graphicsSettings] ?? null)
                ->setBgSoundMute(is_null($bgSoundMute) ? null : (int)($bgSoundMute == "yes"))
                ->setGoodControls(is_null($controls) ? null : (int)($controls == "yes"))
                ->setTimestamp(time())
        );

        return new RedirectResponse($returnUrl."#as-success");
    }

}
