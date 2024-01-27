<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Database\TMarketData;
use AugmentedSteam\Server\Exceptions\MissingParameterException;
use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\DataObjects\DMarketData;
use AugmentedSteam\Server\Model\Market\MarketIndex;
use AugmentedSteam\Server\Model\Money\CurrencyConverter;
use IsThereAnyDeal\Database\DbDriver;
use IsThereAnyDeal\Database\Sql\SqlSelectQuery;
use League\Route\Http\Exception\BadRequestException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class MarketController extends Controller {

    private CurrencyConverter $converter;
    private MarketIndex $index;

    public function __construct(ResponseFactoryInterface $responseFactory, DbDriver $db) {
        parent::__construct($responseFactory, $db);
        $this->converter = new CurrencyConverter($this->db);
        $this->index = new MarketIndex($this->db);
    }

    /**
     * @throws MissingParameterException
     * @throws BadRequestException
     * @deprecated
     */
    public function getAverageCardPricesV1(ServerRequestInterface $request): array {
        $currency = (new Param($request, "currency"))
            ->string();

        $appids = (new Param($request, "appids"))
            ->default([])
            ->list();

        $foilAppids = (new Param($request, "foilappids"))
            ->default([])
            ->list();

        if (count($appids) == 0 && count($foilAppids) == 0) {
            throw new BadRequestException();
        }

        $appids = array_filter(array_map(fn($id) => (int)$id, $appids), fn($id) => $id > 0);
        $foilAppids = array_filter(array_map(fn($id) => (int)$id, $foilAppids), fn($id) => $id > 0);

        $d = new TMarketData();

        $whereSql = [];
        $params = [];
        if (count($appids) != 0) {
            $whereSql[] = "$d->appid IN :appids AND $d->rarity != 'foil'";
            $params[':appids'] = $appids;
        }

        if (count($foilAppids) != 0) {
            $whereSql[] = "$d->appid IN :foilAppids AND $d->rarity = 'foil'";
            $params[':foilAppids'] = $foilAppids;
        }

        $select = (new SqlSelectQuery($this->db,
            "SELECT $d->appid, $d->rarity='foil' as foil, AVG($d->sell_price_usd) as `average`, count(*) as `count`
            FROM $d
            WHERE $d->type='card'
              AND (".implode(" OR ", $whereSql).")
            GROUP BY $d->appid, $d->rarity='foil'
            HAVING `count` > 0"
        ))->params($params)
          ->fetch();

        $conversion = $this->converter->getConversion("USD", $currency);
        $result = [];
        /** $var object $o */
        foreach($select as $o) {
            $appid = $o->appid;
            $isFoil = $o->foil;
            $avg = ($o->average/100)*$conversion;

            $result[$appid][$isFoil ? "foil" : "regular"] = [
                "average" => $avg
            ];
        }

        $this->index->recordRequest(...$appids, ...$foilAppids);
        return $result;
    }

    public function getAverageCardPricesV2(ServerRequestInterface $request): array {
        $currency = (new Param($request, "currency"))
            ->string();

        $appids = (new Param($request, "appids"))
            ->default([])
            ->list();

        if (count($appids) == 0) {
            throw new BadRequestException();
        }

        $appids = array_filter(array_map(fn($id) => (int)$id, $appids), fn($id) => $id > 0);

        $d = new TMarketData();
        $select = (new SqlSelectQuery($this->db,
            "SELECT $d->appid, $d->rarity='foil' as foil, AVG($d->sell_price_usd) as `average`, count(*) as `count`
            FROM $d
            WHERE $d->type='card'
              AND $d->appid IN :appids
            GROUP BY $d->appid, $d->rarity='foil'"
        ))->params([
            ":appids" => $appids
        ])->fetch();

        $conversion = $this->converter->getConversion("USD", $currency);
        $result = [];
        /** $var object $o */
        foreach($select as $o) {
            $appid = $o->appid;
            $isFoil = $o->foil;
            $avg = ($o->average/100)*$conversion;

            $result[$appid][$isFoil ? "foil" : "regular"] = $avg;
        }

        $this->index->recordRequest(...$appids);
        return $result;
    }

    public function getCardsV2(ServerRequestInterface $request): array {
        $currency = (new Param($request, "currency"))
            ->string();

        $appid = (new Param($request, "appid"))
            ->int();

        $d = new TMarketData();
        $select = (new SqlSelectQuery($this->db,
            "SELECT $d->name, $d->img, $d->url, $d->sell_price_usd
            FROM $d
            WHERE $d->appid=:appid
              AND $d->type='card'"
        ))->params([
            ":appid" => $appid
        ])->fetch(DMarketData::class);

        $conversion = $this->converter->getConversion("USD", $currency);

        $result = [];
        /** @var DMarketData $o */
        foreach($select as $o) {
            $result[$o->getName()] = [
                "img" => $o->getImg(),
                "url" => $o->getUrl(),
                "price" => ($o->getSellPriceUsd() / 100) * $conversion,
            ];
        }

        $this->index->recordRequest($appid);
        return $result;
    }
}
