<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Survey;

use AugmentedSteam\Server\Database\TSurveys;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\DataObjects\DSurvey;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Log\LoggerInterface;

class SurveyManager
{
    private DbDriver $db;
    private LoggerInterface $logger;

    public function __construct(
        DbDriver $db,
        LoggerFactoryInterface $loggerFactory
    ) {
        $this->db = $db;
        $this->logger = $loggerFactory->create("survey");
    }

    public function submit(DSurvey $survey): void {

        $s = new TSurveys();
        (new SqlInsertQuery($this->db, $s))
            ->columns(
                $s->appid, $s->steamid,
                $s->framerate, $s->optimized, $s->lag, $s->graphics_settings,
                $s->bg_sound_mute, $s->good_controls, $s->timestamp
            )->onDuplicateKeyUpdate(
                $s->framerate, $s->optimized, $s->lag, $s->graphics_settings,
                $s->bg_sound_mute, $s->good_controls, $s->timestamp
            )->persist($survey);

        $this->logger->info("{$s->appid} by {$s->steamid}");
    }

    private function updateCounter(array &$data, string $key, $value): void {
        if (!is_null($value)) {
            $data[$key][$value]++;
        }
    }

    public function getData(int $appid): ?array {

        $s = new TSurveys();
        $select = (new SqlSelectQuery($this->db,
            "SELECT $s->framerate, $s->optimized, $s->lag, $s->graphics_settings,
                $s->bg_sound_mute, $s->good_controls
            FROM $s
            WHERE $s->appid=:appid"
        ))->params([
            ":appid" => $appid
        ])->fetch(DSurvey::class);

        if (count($select) == 0) {
            return null;
        }

        $data = [
            "framerate" => [
                "0" => 0,
                "30" => 0,
                "60" => 0
            ],
            "settings" => [
                EGraphicsSettings::None => 0,
                EGraphicsSettings::Basic => 0,
                EGraphicsSettings::Granular => 0
            ],
            "optimized" => [0, 0],
            "lag" => [0, 0],
            "bg_sound" => [0, 0],
            "controls" => [0, 0],
        ];

        /** @var DSurvey $o */
        foreach($select as $o) {
            $this->updateCounter($data, "framerate", $o->getFramerate());
            $this->updateCounter($data, "settings", $o->getGraphicsSettings());
            $this->updateCounter($data, "optimized", $o->getOptimized());
            $this->updateCounter($data, "lag", $o->getLag());
            $this->updateCounter($data, "bg_sound", $o->getBgSoundMute());
            $this->updateCounter($data, "controls", $o->getGoodControls());
        }

        return $data;
    }
}
