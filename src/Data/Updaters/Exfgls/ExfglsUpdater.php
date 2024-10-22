<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Updaters\Exfgls;

use AugmentedSteam\Server\Data\Interfaces\ExfglsProviderInterface;
use AugmentedSteam\Server\Database\DExfgls;
use AugmentedSteam\Server\Database\TExfgls;
use IsThereAnyDeal\Database\DbDriver;
use Psr\Log\LoggerInterface;

class ExfglsUpdater
{
    private const int CacheLimit = 30*86400;
    private const int BatchSize = 1000;

    public function __construct(
        private readonly DbDriver $db,
        private readonly ExfglsProviderInterface $provider,
        private readonly LoggerInterface $logger,
    ) {}

    public function update(): void {

        $e = new TExfgls();
        /** @var list<int> $appids */
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

        $insert = $this->db->insert($e)
            ->stackSize(50)
            ->columns($e->appid, $e->excluded, $e->checked, $e->timestamp)
            ->onDuplicateKeyUpdate($e->excluded, $e->checked, $e->timestamp);

        $data = $this->provider->fetch($appids);
        foreach($appids as $appid) {
            $insert->stack(
                (new DExfgls())
                    ->setAppid($appid)
                    ->setChecked(true) // isset($json[(string)$appid])
                    ->setExcluded($data[$appid] ?? false)
                    ->setTimestamp(time())
            );
        }
        $insert->persist();

        $this->logger->info("Update done", [$appids, $data]);
    }
}
