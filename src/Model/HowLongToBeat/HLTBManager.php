<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\HowLongToBeat;

use AugmentedSteam\Server\Loader\Proxy\ProxyFactoryInterface;
use AugmentedSteam\Server\Loader\Proxy\ProxyInterface;
use AugmentedSteam\Server\Loader\SimpleLoader;
use AugmentedSteam\Server\Logging\LoggerFactoryInterface;
use AugmentedSteam\Server\Model\DataObjects\DHLTB;
use AugmentedSteam\Server\Model\Tables\THLTB;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use IsThereAnyDeal\Database\Sql\SqlUpdateObjectQuery;
use Psr\Log\LoggerInterface;

class HLTBManager {
    private const RecheckLimit = 14*86400;

    private DbDriver $db;
    private SimpleLoader $loader;
    private LoggerInterface $logger;
    private ProxyInterface $proxy;

    private THLTB $h;

    public function __construct(
        DbDriver $db,
        SimpleLoader $loader,
        LoggerFactoryInterface $loggerFactory,
        ProxyFactoryInterface $proxyFactory
    ) {
        $this->db = $db;
        $this->loader = $loader;
        $this->logger = $loggerFactory->createLogger("hltb");
        $this->proxy = $proxyFactory->createProxy();

        $this->h = new THLTB();
    }

    public function getData(int $appid): ?DHLTB {
        $h = $this->h;

        /** @var ?DHLTB $data */
        $data = (new SqlSelectQuery($this->db,
            "SELECT $h->id, $h->main, $h->extra, $h->complete, $h->checked_timestamp
            FROM $h
            WHERE $h->appid=:appid"
        ))->params([
            ":appid" => $appid
        ])->fetch(DHLTB::class)
          ->getOne();

        if (is_null($data)) {
            return null;
        }

        if ($data->getCheckedTimestamp() < time() - self::RecheckLimit) {
            return $this->recheck($data->getId(), $appid) ?? $data;
        }

        return $data;
    }

    private function recheck(int $id, int $appid): ?DHLTB {
        $response = $this->loader->get(
            "https://howlongtobeat.com/game.php?id=$id",
            $this->proxy->getCurlOptions()
        );

        if (!is_null($response)) {
            $data = (new GamePageParser())
                ->parse($response->getBody()->getContents());

            $data = (new DHLTB())
                ->setId($id)
                ->setAppid($data['appid'] ?? null)
                ->setMain($data['times']['main'] ?? null)
                ->setExtra($data['times']['extra'] ?? null)
                ->setComplete($data['times']['complete'] ?? null)
                ->setCheckedTimestamp(time());

            $h = $this->h;
            (new SqlUpdateObjectQuery($this->db, $h))
                ->columns($h->appid, $h->main, $h->extra, $h->complete, $h->checked_timestamp)
                ->where($h->id)
                ->update($data);

            $this->logger->info("Updated $id");
            return $data->getAppid() == $appid
                ? $data
                : null;
        }

        $this->logger->error("Couldn't update $id");
        return null;
    }
}
