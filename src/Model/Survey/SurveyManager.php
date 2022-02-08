<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Survey;

use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\DataObjects\DSurvey;
use AugmentedSteam\Server\Model\Tables\TSurveys;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
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
        $this->logger = $loggerFactory->createLogger("survey");
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
}
