<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Model\Market;

use AugmentedSteam\Server\Database\DMarketData;
use AugmentedSteam\Server\Database\TMarketData;
use IsThereAnyDeal\Database\DbDriver;

class MarketManager
{
    public function __construct(
        private readonly DbDriver $db
    ) {}

    /**
     * @param list<int> $appids
     * @return array<int, array<"foil"|"regular", float>>
     */
    public function getAverageCardPrices(array $appids, float $conversion): array {

        $d = new TMarketData();
        $select = $this->db->select(<<<SQL
            SELECT $d->appid, $d->rarity=:foil as foil, AVG($d->sell_price_usd) as `average`, count(*) as `count`
            FROM $d
            WHERE $d->type=:card
              AND $d->appid IN :appids
            GROUP BY $d->appid, $d->rarity=:foil
            SQL
        )->params([
            ":appids" => $appids,
            ":foil" => ERarity::Foil,
            ":card" => EType::Card
        ])->fetch();

        $result = [];
        /** $var object $o */
        foreach($select as $o) {
            $appid = $o->appid;
            $isFoil = $o->foil;
            $avg = ($o->average/100)*$conversion;

            $result[$appid][$isFoil ? "foil" : "regular"] = $avg;
        }
        return $result;
    }

    /**
     * @return array<string, array{
     *     img: string,
     *     url: string,
     *     price: float
     * }>
     */
    public function getCards(int $appid, float $conversion): array {

        $d = new TMarketData();
        $select = $this->db->select(<<<SQL
            SELECT $d->name, $d->img, $d->url, $d->sell_price_usd
            FROM $d
            WHERE $d->appid=:appid
              AND $d->type=:card
            SQL
        )->params([
            ":appid" => $appid,
            ":card" => EType::Card
        ])->fetch(DMarketData::class);

        $result = [];
        /** @var DMarketData $o */
        foreach($select as $o) {
            $result[$o->getName()] = [
                "img" => $o->getImg(),
                "url" => $o->getUrl(),
                "price" => ($o->getSellPriceUsd() / 100) * $conversion,
            ];
        }
        return $result;
    }

    public function doesBackgroundExist(int $appid, string $img): bool {

        $d = new TMarketData();
        return $this->db->select(<<<SQL
            SELECT 1
            FROM $d
            WHERE $d->appid=:appid
              AND $d->img=:img
              AND $d->type=:background
            SQL
        )->exists([
            ":appid" =>$appid,
            ":img" => $img,
            ":background" => EType::Background
        ]);
    }

    /**
     * @param int $appid
     * @return list<array{string, string}>
     */
    public function getBackgrounds(int $appid): iterable {

        $d = new TMarketData();
        return $this->db->select(<<<SQL
            SELECT $d->name, $d->img
            FROM $d
            WHERE $d->appid=:appid
              AND $d->type=:background
            ORDER BY $d->name ASC
            SQL
        )->params([
            ":appid" => $appid,
            ":background" => EType::Background
        ])->fetch(DMarketData::class)
          ->toArray(fn(DMarketData $o) => [
              $o->getImg(),
              preg_replace("#\s*\(Profile Background\)#", "", $o->getName()),
          ]);
    }

    /**
     * @return list<array{int, string}>
     */
    public function getGamesWithBackgrounds(): array {

        $d = new TMarketData();
        return $this->db->select(<<<SQL
            SELECT DISTINCT $d->appid, $d->appname
            FROM $d
            WHERE $d->type=:background
            ORDER BY $d->appname ASC
            SQL
        )->params([
            ":background" => EType::Background
        ])->fetch(DMarketData::class)
          ->toArray(fn(DMarketData $o) => [$o->getAppid(), $o->getAppName()]);
    }
}
