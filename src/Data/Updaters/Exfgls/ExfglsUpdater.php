<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Updaters\Exfgls;

use AugmentedSteam\Server\Database\TExfgls;
use AugmentedSteam\Server\Model\DataObjects\DExfgls;
use IsThereAnyDeal\Database\DbDriver;
use Psr\Log\LoggerInterface;

class ExfglsUpdater
{
    private const int CacheLimit = 30*86400;
    private const int BatchSize = 100;

    public function __construct(
        private readonly DbDriver $db,
        private readonly LoggerInterface $logger,
        private readonly ExfglsConfig $config
    ) {}

    public function update(): void {
        if (!$this->config->isEnabled()) { return; }

        $e = new TExfgls();
        $appids = $this->db->select(<<<SQL
            SELECT $e->appid
            FROM $e
            WHERE $e->checked=0
               OR $e->timestamp < :timestamp
            ORDER BY $e->checked=0 DESC,
                     $e->timestamp ASC
            LIMIT :limit
            SQL
        )->params([
            ":timestamp" => time() - self::CacheLimit,
            ":limit" => self::BatchSize
        ])->fetchValueArray();

        if (count($appids) == 0) {
            $this->logger->info("Nothing to update");
            return;
        }

        $bin = $this->config->getBin();
        $user = $this->config->getUser();
        $password = $this->config->getPassword();
        $logPath = LOGS_DIR."/".date("Y-m-d").".exfgls.log";

        $appidsParam = implode(",", $appids);
        $result = exec(BIN_DIR."/$bin \"$user\" \"$password\" \"$appidsParam\" \"$logPath\"");
        $json = json_decode($result, true, flags: JSON_THROW_ON_ERROR);

        $insert = $this->db->insert($e)
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
