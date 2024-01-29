<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Updaters\Rates;

use AugmentedSteam\Server\Data\Interfaces\RatesProviderInterface;
use AugmentedSteam\Server\Database\TCurrency;
use AugmentedSteam\Server\Model\DataObjects\DCurrency;
use IsThereAnyDeal\Database\DbDriver;
use Psr\Log\LoggerInterface;

class RatesUpdater {
    public function __construct(
        private readonly DbDriver $db,
        private readonly RatesProviderInterface $provider,
        private readonly LoggerInterface $logger
    ) {}

    public function update(): void {
        $this->logger->info("Start");

        $rates = $this->provider->fetch();
        if (empty($rates)) {
            throw new \Exception("No data");
        }

        $timestamp = time();
        $c = new TCurrency();

        $this->db->begin();
        $insert = $this->db->insert($c)
            ->stackSize(1000)
            ->columns($c->from, $c->to, $c->rate, $c->timestamp)
            ->onDuplicateKeyUpdate($c->rate, $c->timestamp);

        foreach($rates as $data) {
            $insert->stack((new DCurrency())
                ->setFrom($data['from'])
                ->setTo($data['to'])
                ->setRate($data['rate'])
                ->setTimestamp($timestamp)
            );
        }
        $insert->persist();

        $this->db->delete(<<<SQL
            DELETE FROM $c
            WHERE $c->timestamp != :timestamp
            SQL
        )->delete([
            ":timestamp" => $timestamp
        ]);

        $this->logger->info("Done");
    }
}
