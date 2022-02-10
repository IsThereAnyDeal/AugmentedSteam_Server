<?php
namespace AugmentedSteam\Server\Model\SteamPeek;

use AugmentedSteam\Server\Config\EndpointsConfig;
use AugmentedSteam\Server\Config\KeysConfig;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\DataObjects\DSimilar;
use AugmentedSteam\Server\Model\Tables\TSimilar;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Log\LoggerInterface;

class SteamPeekManager {
    private const CacheLimit = 10*86400;

    private DbDriver $db;
    private LoggerInterface $logger;
    private SimpleLoader $loader;
    private EndpointsConfig $config;
    private KeysConfig $keysConfig;

    private TSimilar $s;

    public function __construct(
        DbDriver $db,
        SimpleLoader $loader,
        EndpointsConfig $config,
        KeysConfig $keysConfig,
        LoggerFactoryInterface $loggerFactory
    ) {
        $this->db = $db;
        $this->loader = $loader;
        $this->config = $config;
        $this->keysConfig = $keysConfig;
        $this->logger = $loggerFactory->createLogger("steampeek");

        $this->s = new TSimilar();
    }

    /**
     * @param int $appid
     * @param int $preferedCount
     * @param bool $randomOrder
     * @return SteamPeekGame[]
     */
    public function getSimilar(int $appid, int $preferedCount=5, bool $randomOrder=false): array {
        $s = $this->s;

        /** @var ?DSimilar $data */
        $data = (new SqlSelectQuery($this->db,
            "SELECT $s->data
            FROM $s
            WHERE $s->appid=:appid
              AND $s->timestamp >= :timestamp"
        ))->params([
            ":appid" => $appid,
            ":timestamp" => time() - self::CacheLimit
        ])->fetch(DSimilar::class)
          ->getOne();

        $results = is_null($data)
            ? $this->refresh($appid)
            : $data->getData();

        if (is_null($results)) {
            return [];
        }

        if ($randomOrder) {
            shuffle($results);
        }

        return array_map(
            fn($value) => new SteamPeekGame($value),
            array_slice($results, 0, $preferedCount)
        );
    }

    private function refresh(int $appid): ?array {
        $endpoint = $this->config->getSteamPeekEndpoint($appid, $this->keysConfig->getSteamPeekApiKey());

        $data = (new DSimilar())
            ->setAppid($appid)
            ->setData(null)
            ->setTimestamp(time());

        $results = null;
        $response = $this->loader->get($endpoint);

        if (!is_null($response)) {
            $json = json_decode($response->getBody()->getContents(), true);

            if (!empty($json['response']['success']) && $json['response']['success'] == 1) {
                $results = $json['response']['results'];
                $data->setData($results);
            }
        }

        $s = $this->s;
        (new SqlInsertQuery($this->db, $s))
            ->columns($s->appid, $s->data, $s->timestamp)
            ->onDuplicateKeyUpdate($s->data, $s->timestamp)
            ->persist($data);

        if (!is_null($data->getData())) {
            $this->logger->info((string)$appid);
        } else {
            $this->logger->error((string)$appid);
        }

        return $results;
    }

}
