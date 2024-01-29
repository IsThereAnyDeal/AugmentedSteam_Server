<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\PricesProviderInterface;
use AugmentedSteam\Server\Data\Managers\GameIdsManager;
use AugmentedSteam\Server\Http\ListParam;
use AugmentedSteam\Server\Http\StringParam;
use IsThereAnyDeal\Database\DbDriver;
use League\Route\Http\Exception\BadRequestException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class PricesController extends Controller {

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        DbDriver $db,
        private readonly GameIdsManager $gameIdsManager,
        private readonly PricesProviderInterface $pricesProvider
    ) {
        parent::__construct($responseFactory, $db);
    }

    public function getPrices_v2(ServerRequestInterface $request): ?array {
        $country = (new StringParam($request, "country"))->value();
        $shops = (new ListParam($request, "shops", default: []))->value();
        $appids = (new ListParam($request, "appids", default: []))->value();
        $subids = (new ListParam($request, "subids", default: []))->value();
        $bundleids = (new ListParam($request, "bundleids", default: []))->value();

        $ids = array_merge(
            array_map(fn($id) => "app/$id", array_filter($appids)),
            array_map(fn($id) => "sub/$id", array_filter($subids)),
            array_map(fn($id) => "bundle/$id", array_filter($bundleids)),
        );

        if (count($ids) == 0) {
            throw new BadRequestException();
        }

        $map = $this->gameIdsManager->getIdMap($ids);
        $gids = array_values($map);
        $prices = $this->pricesProvider->fetch($gids, $shops, $country);

        $result = [];
        /* TODO finish results
        foreach($map as $steamId => $gid) {
            $result[$steamId] = $prices[$gid] ?? null;
        }
        */
        return $result;
    }
}
