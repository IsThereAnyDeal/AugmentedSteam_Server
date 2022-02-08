<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Config\CoreConfig;
use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\DataObjects\DSurvey;
use AugmentedSteam\Server\Model\Survey\EGraphicsSettings;
use AugmentedSteam\Server\Model\Survey\SurveyManager;
use AugmentedSteam\Server\OpenId\OpenId;
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

    public function getSubmitV1(ServerRequestInterface $request): RedirectResponse {
        $returnUrl = "https://store.steampowered.com/";

        try {
            $appid = (new Param($request, "appid"))->int();
        } catch(\Exception $e) {
            return new RedirectResponse($returnUrl."#as-error:badrequest");
        }

        $returnUrl = "https://store.steampowered.com/app/{$appid}/";
        try {
            $steamId = (new Param($request, "profile"))->int();
        } catch(\Exception $e) {
            return new RedirectResponse($returnUrl."#as-error:badrequest");
        }

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

        $openId = new OpenId($this->config->getHost(), "/v1/survey/submit/?$params");
        if (!$openId->isAuthenticationStarted()) {
            return new RedirectResponse($openId->getAuthUrl()->toString());
        }

        if (!$openId->authenticate()) {
            return new RedirectResponse($returnUrl."#as-failure");
        }

        $this->surveyManager->submit(
            (new DSurvey())
                ->setAppid($appid)
                ->setSteamId($steamId)
                ->setFramerate(["th" => 30, "sx" => 60, "va" => 0][$framerate] ?? null)
                ->setOptimized(is_null($optimized) ? null : $optimized == "yes")
                ->setLag(is_null($lag) ? null : $lag == "yes")
                ->setGraphicsSettings([
                        "no" => EGraphicsSettings::None,
                        "bs" => EGraphicsSettings::Basic,
                        "gr" => EGraphicsSettings::Granular
                    ][$graphicsSettings] ?? null)
                ->setBgSoundMute(is_null($bgSoundMute) ? null : $bgSoundMute == "yes")
                ->setGoodControls(is_null($controls) ? null : $controls == "yes")
                ->setTimestamp(time())
        );

        return new RedirectResponse($returnUrl."#as-success");
    }

}
