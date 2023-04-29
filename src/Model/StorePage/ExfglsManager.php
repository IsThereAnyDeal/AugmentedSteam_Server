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
    private const BatchSize = 100;

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
            WHERE $e->appid=:appid"
        ))->params([
            ":appid" => $appid
        ])->fetch(DExfgls::class)
          ->getOne();

        if (is_null($data)) {
            $data = (new DExfgls())
                ->setAppid($appid)
                ->setExcluded(false)
                ->setChecked(false)
                ->setTimestamp(time());

            (new SqlInsertQuery($this->db, $e))
                ->columns($e->appid, $e->excluded, $e->checked, $e->timestamp)
                ->onDuplicateKeyUpdate($e->excluded, $e->checked, $e->timestamp)
                ->persist($data);
        }

        return $data;
    }

    public function update(): void {
        if (!$this->config->isEnabled()) { return; }
        $e = $this->e;

        $appids = (new SqlSelectQuery($this->db,
            "SELECT $e->appid
            FROM $e
            WHERE $e->checked=0
              OR $e->timestamp < :timestamp
            ORDER BY $e->checked=0 DESC, $e->timestamp ASC
            LIMIT ".self::BatchSize
        ))->params([
            ":timestamp" => time() - self::CacheLimit
        ])->fetch(DExfgls::class)
          ->map(fn(DExfgls $o) => $o->getAppid())
          ->toArray();

        if (count($appids) == 0) {
            $this->logger->info("Nothing to update");
            return;
        }

        $bin = $this->config->getBin();
        $user = $this->config->getUser();
        $password = $this->config->getPassword();
        $logPath = __DIR__."/../../../logs/".date("Y-m-d").".exfgls.log";

        $appidsParam = implode(",", $appids);

        $result = exec(__DIR__."/../../../bin/$bin \"$user\" \"$password\" \"$appidsParam\" \"$logPath\"");
        $json = json_decode($result, true);

        $insert = (new SqlInsertQuery($this->db, $e))
            ->stackSize(50)
            ->columns($e->appid, $e->excluded, $e->checked, $e->timestamp)
            ->onDuplicateKeyUpdate($e->excluded, $e->checked, $e->timestamp);

        foreach($appids as $appid) {
            $insert->stack(
                (new DExfgls())
                    ->setAppid($appid)
                    ->setChecked(true) // isset($json[(string)$appid])
                    ->setExcluded($json[(string)$appid] ?? false)
                    ->setTimestamp(time())
            );
        }

        $insert->persist();
        $this->logger->info("Update done", [$appids, $json]);
    }
}
