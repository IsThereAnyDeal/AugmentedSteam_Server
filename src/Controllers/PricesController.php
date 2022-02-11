<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\Prices\PricesManager;
use IsThereAnyDeal\Database\DbDriver;
use League\Route\Http\Exception\BadRequestException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class PricesController extends Controller {

    private PricesManager $pricesManager;

    public function __construct(ResponseFactoryInterface $responseFactory, DbDriver $db, PricesManager $pricesManager) {
        parent::__construct($responseFactory, $db);
        $this->pricesManager = $pricesManager;
    }

    public function getPricesV1(ServerRequestInterface $request): array {
        $stores = (new Param($request, "stores"))->default([])->list();
        $cc = (new Param($request, "cc"))->default(null)->string();
        $coupon = (new Param($request, "coupon"))->default(false)->bool();
        $appids = (new Param($request, "appids"))->default([])->list();
        $subids = (new Param($request, "subids"))->default([])->list();
        $bundleids = (new Param($request, "bundleids"))->default([])->list();

        $ids = array_merge(
            array_map(fn($id) => "app/$id", array_filter($appids)),
            array_map(fn($id) => "sub/$id", array_filter($subids)),
            array_map(fn($id) => "bundle/$id", array_filter($bundleids)),
        );

        if (count($ids) == 0) {
            throw new BadRequestException();
        }

        return $this->pricesManager->getData($ids, $cc, $stores, $coupon) ?? [];
    }

    public function getPricesV2(ServerRequestInterface $request): ?array {
        $stores = (new Param($request, "stores"))->default([])->list();
        $cc = (new Param($request, "cc"))->default(null)->string();
        $coupon = (new Param($request, "coupon"))->default(false)->bool();
        $appids = (new Param($request, "appids"))->default([])->list();
        $subids = (new Param($request, "subids"))->default([])->list();
        $bundleids = (new Param($request, "bundleids"))->default([])->list();

        $ids = array_merge(
            array_map(fn($id) => "app/$id", array_filter($appids)),
            array_map(fn($id) => "sub/$id", array_filter($subids)),
            array_map(fn($id) => "bundle/$id", array_filter($bundleids)),
        );

        if (count($ids) == 0) {
            throw new BadRequestException();
        }

        $data = $this->pricesManager->getData($ids, $cc, $stores, $coupon);
        return $data['data'] ?? null;
    }
}
