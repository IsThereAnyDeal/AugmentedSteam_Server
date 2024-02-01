<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\PlayersProviderInterface;
use AugmentedSteam\Server\Data\Managers\ExfglsManager;
use AugmentedSteam\Server\Data\Managers\HLTBManager;
use AugmentedSteam\Server\Data\Managers\ReviewsManager;
use AugmentedSteam\Server\Data\Managers\WSGFManager;
use Psr\Http\Message\ServerRequestInterface;

class StorePageController extends Controller
{
    public function __construct(
        private readonly WSGFManager $wsgfManager,
        private readonly ExfglsManager $exfglsManager,
        private readonly HLTBManager $hltbManager,
        private readonly ReviewsManager $reviewsManager,
        private readonly PlayersProviderInterface $players,
    ) {
    }

    public function getAppInfo_v2(ServerRequestInterface $request, array $params) {
        $appid = (int)$params['appid'];

        $wsgf = $this->wsgfManager->getData($appid);
        $exfgls = $this->exfglsManager->getData($appid);
        $hltb = $this->hltbManager->getData($appid);
        $reviews = $this->reviewsManager->getData($appid);
        $players = $this->players->fetch($appid);

        return [
            "family_sharing" => !$exfgls->isExcluded(),
            "players" => [
                "recent" => $players->current,
                "peak_today" => $players->peakToday,
                "peak_all" => $players->peakAll,
            ],
            "wsgf" => is_null($wsgf) ? null : [
                "url" => $wsgf->path,
                "wide" => $wsgf->wideScreenGrade,
                "ultrawide" => $wsgf->ultraWideScreenGrade,
                "multi_monitor" => $wsgf->multiMonitorGrade,
                "4k" => $wsgf->grade4k,
            ],
            "hltb" => is_null($hltb) ? null : [
                "story" => $hltb->getMain(),
                "extras" => $hltb->getExtra(),
                "complete" => $hltb->getComplete(),
                "url" => "https://howlongtobeat.com/game/{$hltb->getId()}"
            ],
            "reviews" => [
                "metauser" => is_null($reviews->metauser) ? null : [
                    "score" => $reviews->metauser->score,
                    "verdict" => $reviews->metauser->verdict,
                    "url" => $reviews->metauser->url,
                ],
                "opencritic" => is_null($reviews->opencritic) ? null : [
                    "score" => $reviews->opencritic->score,
                    "verdict" => $reviews->opencritic->verdict,
                    "url" => $reviews->opencritic->url,
                ]
            ]
        ];
    }
}
