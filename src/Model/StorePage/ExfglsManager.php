<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\StorePage;

use AugmentedSteam\Server\Config\ExfglsConfig;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\DataObjects\DExfgls;
use AugmentedSteam\Server\Model\Tables\TExfgls;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlInsertQuery;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use Psr\Log\LoggerInterface;

class ExfglsManager
{
    private const CacheLimit = 30*86400;

    private DbDriver $db;
    private LoggerInterface $logger;
    private ExfglsConfig $config;

    private TExfgls $e;

    public function __construct(
        DbDriver $db,
        LoggerFactoryInterface $loggerFactory,
        ExfglsConfig $config
    ) {
        $this->db = $db;
        $this->logger = $loggerFactory->createLogger("exfgls");
        $this->config = $config;

        $this->e = new TExfgls();
    }

    public function getData(int $appid): DExfgls {
        $e = $this->e;

        $data = (new SqlSelectQuery($this->db,
            "SELECT $e->excluded
            FROM $e
            WHERE $e->appid=:appid
              AND $e->timestamp >= :timestamp"
        ))->params([
            ":appid" => $appid,
            ":timestamp" => time() - self::CacheLimit
        ])->fetch(DExfgls::class)
          ->getOne();

        return is_null($data)
            ? $this->refresh($appid)
            : $data;
    }

    private function refresh(int $appid): DExfgls {

        $data = (new DExfgls())
            ->setAppid($appid)
            ->setChecked(false)
            ->setExcluded(false)
            ->setTimestamp(time());

        if ($this->config->isEnabled()) {
            $bin = $this->config->getBin();
            $user = $this->config->getUser();
            $password = $this->config->getPassword();
            $logPath = __DIR__."/../../../logs/".date("Y-m-d").".exfgls.log";

            $result = exec(__DIR__."/../../../bin/$bin \"$user\" \"$password\" $appid \"$logPath\"");
            $json = json_decode($result, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($json[(string)$appid])) {
                $data
                    ->setExcluded($json[(string)$appid])
                    ->setChecked(true);
            }
        }

        $e = $this->e;
        (new SqlInsertQuery($this->db, $e))
            ->columns($e->appid, $e->excluded, $e->checked, $e->timestamp)
            ->onDuplicateKeyUpdate($e->excluded, $e->checked, $e->timestamp)
            ->persist($data);

        $this->logger->info((string)$appid);
        return $data;
    }
}
