<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\HowLongToBeat\HLTBManager;
use AugmentedSteam\Server\Model\Reviews\ReviewsManager;
use AugmentedSteam\Server\Model\StorePage\ExfglsManager;
use AugmentedSteam\Server\Model\StorePage\SteamChartsManager;
use AugmentedSteam\Server\Model\StorePage\SteamSpyManager;
use AugmentedSteam\Server\Model\StorePage\WSGFManager;
use AugmentedSteam\Server\Model\Survey\SurveyManager;
use IsThereAnyDeal\Database\DbDriver;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class StorePageController extends Controller
{
    private SteamChartsManager $steamChartManager;
    private SteamSpyManager $steamSpyManager;
    private WSGFManager $wsgfManager;
    private ExfglsManager $exfglsManager;
    private HLTBManager $hltbManager;
    private ReviewsManager $reviewsManager;
    private SurveyManager $surveyManager;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        DbDriver $db,
        SteamChartsManager $steamChartManager,
        SteamSpyManager $steamSpyManager,
        WSGFManager $wsgfManager,
        ExfglsManager $exfglsManager,
        HLTBManager $hltbManager,
        ReviewsManager $reviewsManager,
        SurveyManager $surveyManager
    ) {
        parent::__construct($responseFactory, $db);

        $this->steamChartManager = $steamChartManager;
        $this->steamSpyManager = $steamSpyManager;
        $this->wsgfManager = $wsgfManager;
        $this->exfglsManager = $exfglsManager;
        $this->hltbManager = $hltbManager;
        $this->reviewsManager = $reviewsManager;
        $this->surveyManager = $surveyManager;
    }

    /** @deprecated */
    public function getStorePageDataV1(ServerRequestInterface $request) {
        $appid = (new Param($request, "appid"))->int();

        $result = [];

        //

        $steamCharts = $this->steamChartManager->getData($appid);
        $result['charts']['chart'] = [
            "current" => number_format($steamCharts->getRecent()),
            "peaktoday" => number_format($steamCharts->getPeakDay()),
            "peakall" => number_format($steamCharts->getPeakAll()),
        ];

        //

        $steamSpy = $this->steamSpyManager->getData($appid);
        $result['steamspy'] = [
            "owners" => $steamSpy->getOwners(),
            "owners_variance" => null,
            "players_forever" => null,
            "players_forever_variance" => null,
            "players_2weeks" => null,
            "players_2weeks_variance" => null,
            "average_forever" => $steamSpy->getAverageForever(),
            "average_2weeks" => $steamSpy->getAverage2weeks(),
        ];

        $wsgf = $this->wsgfManager->getData($appid);
        if (!empty($wsgf)) {
            $result['wsgf'] = [
                "Title" => $wsgf->getTitle(),
                "SteamID" => $wsgf->getSteamId(),
                "Path" => $wsgf->getPath(),
                "WideScreenGrade" => $wsgf->getWideScreenGrade(),
                "MultiMonitorGrade" => $wsgf->getMultiMonitorGrade(),
                "UltraWideScreenGrade" => $wsgf->getUltraWideScreenGrade(),
                "Grade4k" => $wsgf->getGrade4k(),
                "Nid" => $wsgf->getNid()
            ];
        }

        //

        $exfgls = $this->exfglsManager->getData($appid);
        $result['exfgls'] = [
            "appid" => $appid,
            "excluded" => $exfgls->isExcluded()
        ];

        //

        $hltb = $this->hltbManager->getData($appid);
        if (!is_null($hltb)) {
            $result['hltb'] = [
                "success" => true,
                "main_story" => $hltb->getMainString(),
                "main_extras" => $hltb->getExtraString(),
                "comp" => $hltb->getCompleteString(),
                "url" => "https://howlongtobeat.com/game.php?id={$hltb->getId()}",
                "submit_url" => "https://howlongtobeat.com/submit.php?s=add&gid={$hltb->getId()}",
            ];
        } else {
            $result['hltb']['success'] = false;
        }

        //

        $reviews = $this->reviewsManager->getData($appid);
        if (!is_null($reviews)) {
            $metacritic = $reviews->getMetaCritic();
            if (!is_null($metacritic)) {
                $result['data']['userscore'] = $metacritic->getUserScore()/10;
            }

            $opencritic = $reviews->getOpenCritic();
            if (!is_null($opencritic)) {
                $result['oc'] = [
                    "url" => $opencritic->getUrl(),
                    "score" => $opencritic->getScore(),
                    "award" => $opencritic->getAward(),
                    "reviews" => []
                ];

                foreach($opencritic->getReviews() as $r) {
                    $result['oc']['reviews'][] = [
                        "date" => $r->getPublishedDate(),
                        "snippet" => $r->getSnippet(),
                        "dScore" => $r->getDisplayScore(),
                        "rUrl" => $r->getExternalUrl(),
                        "author" => $r->getAuthor(),
                        "name" => $r->getOutletName()
                    ];
                }
            }
        }

        //

        $data = $this->surveyManager->getData($appid);
        if (!is_null($data)) {
            $result['survey'] = $data;
        }

        return $result;
    }


    public function getGameInfoV2(ServerRequestInterface $request, array $params) {
        $appid = (int)$params['appid'];

        $steamCharts = $this->steamChartManager->getData($appid);
        $steamSpy = $this->steamSpyManager->getData($appid);
        $wsgf = $this->wsgfManager->getData($appid);
        $exfgls = $this->exfglsManager->getData($appid);
        $hltb = $this->hltbManager->getData($appid);
        $reviews = $this->reviewsManager->getData($appid);
        $survey = $this->surveyManager->getData($appid);

        $result = [
            "family_sharing" => !$exfgls->isExcluded(),
            "steamcharts" => [
                "players" => [
                    "recent" => $steamCharts->getRecent(),
                    "peak_today" => $steamCharts->getPeakDay(),
                    "peak_all" => $steamCharts->getPeakAll(),
                ]
            ],
            "steamspy" => [
                "owners" => $steamSpy->getOwnersRange(),
                "playtime" => [
                    "2weeks" => $steamSpy->getAverage2weeks(),
                    "forever" => $steamSpy->getAverageForever(),
                ]
            ],
            "wsgf" => null,
            "hltb" => null,
            "metacritic" => null,
            "survey" => null
        ];

        if (!empty($wsgf)) {
            $result['wsgf'] = [
                "url" => $wsgf->getPath(),
                "wide" => $wsgf->getWideScreenGrade(),
                "ultrawide" => $wsgf->getUltraWideScreenGrade(),
                "multi_monitor" => $wsgf->getMultiMonitorGrade(),
                "4k" => $wsgf->getGrade4k(),
            ];
        }
        if (!is_null($hltb)) {
            $result['hltb'] = [
                "id" => $hltb->getId(),
                "story" => $hltb->getMain(),
                "extras" => $hltb->getExtra(),
                "complete" => $hltb->getComplete()
            ];
        }

        if (!is_null($reviews)) {
            $metacritic = $reviews->getMetaCritic();
            if (!is_null($metacritic)) {
                $result['metacritic']['userscore'] = $metacritic->getUserScore();
            }

            $opencritic = $reviews->getOpenCritic();
            if (!is_null($opencritic)) {
                $result['opencritic'] = [
                    "url" => $opencritic->getUrl(),
                    "score" => $opencritic->getScore(),
                    "award" => $opencritic->getAward(),
                    "reviews" => []
                ];

                foreach($opencritic->getReviews() as $r) {
                    $result['opencritic']['reviews'][] = [
                        "date" => $r->getPublishedDate(),
                        "snippet" => $r->getSnippet(),
                        "score" => $r->getDisplayScore(),
                        "url" => $r->getExternalUrl(),
                        "author" => $r->getAuthor(),
                        "name" => $r->getOutletName()
                    ];
                }
            }
        }

        if (!is_null($survey)) {
            $result['survey'] = $survey;
        }

        return $result;
    }
}
