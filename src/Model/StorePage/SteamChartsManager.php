<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\StorePage;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\DataObjects\DSteamCharts;
use AugmentedSteam\Server\Model\Tables\TSteamCharts;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Log\LoggerInterface;

class SteamChartsManager
{
    private const CacheLimit = 900;

    private DbDriver $db;
    private SimpleLoader $loader;
    private LoggerInterface $logger;
    private EndpointsConfig $config;

    private TSteamCharts $c;

    private const AppidRemap = [
        201270 => 34330,
		262341 => 39210,
        262342 => 39210
	];

    public function __construct(DbDriver $db, SimpleLoader $loader, LoggerFactoryInterface $loggerFactory, EndpointsConfig $config) {
        $this->db = $db;
        $this->loader = $loader;
        $this->logger = $loggerFactory->createLogger("steamcharts");
        $this->config = $config;

        $this->c = new TSteamCharts();
    }

    public function getData(int $appid): ?DSteamCharts {
        $c = $this->c;
        $appid = self::AppidRemap[$appid] ?? $appid;

        $data = (new SqlSelectQuery($this->db,
            "SELECT $c->recent, $c->peak_day, $c->peak_all
            FROM $c
            WHERE $c->appid=:appid
              AND $c->timestamp >= :timestamp"
        ))->params([
            ":appid" => $appid,
            ":timestamp" => time() - self::CacheLimit
        ])->fetch(DSteamCharts::class)
          ->getOne();

        if (is_null($data)) {
            $data = $this->refresh($appid);
        }

        return $data;
    }

    private function refresh(int $appid): DSteamCharts {
        $url = $this->config->getSteamChartsEndpoint($appid);

        $hasData = false;
        $data = (new DSteamCharts())
            ->setAppid($appid)
            ->setRecent(null)
            ->setPeakDay(null)
            ->setPeakAll(null)
            ->setTimestamp(time());

        $response = $this->loader->get($url);
        if (!is_null($response)) {
            $dom = new \DOMDocument();

            if ($dom->loadHTML($response->getBody()->getContents())) {
                $xpath = new \DOMXPath($dom);
                $nodes = $xpath->query("//div[@class='app-stat']/span[@class='num']");
                if (count($nodes) == 3) {
                    $recent = (int)str_replace(",", "", $nodes->item(0)->nodeValue);
                    $oneDay = (int)str_replace(",", "", $nodes->item(1)->nodeValue);
                    $allTime = (int)str_replace(",", "", $nodes->item(2)->nodeValue);

                    $hasData = true;
                    $data
                        ->setRecent($recent)
                        ->setPeakDay($oneDay)
                        ->setPeakAll($allTime);
                }
            }
        }

        $c = $this->c;
        (new SqlInsertQuery($this->db, $c))
            ->columns($c->appid, $c->recent, $c->peak_day, $c->peak_all, $c->timestamp)
            ->onDuplicateKeyUpdate($c->recent, $c->peak_day, $c->peak_all, $c->timestamp)
            ->persist($data);

        if ($hasData) {
            $this->logger->info($appid);
        } else {
            $this->logger->error($appid);
        }
        return $data;
    }
}
