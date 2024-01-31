<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Exceptions\InvalidValueException;
use AugmentedSteam\Server\Http\IntParam;
use AugmentedSteam\Server\Http\ListParam;
use AugmentedSteam\Server\Http\StringParam;
use AugmentedSteam\Server\Model\Market\MarketIndex;
use AugmentedSteam\Server\Model\Market\MarketManager;
use AugmentedSteam\Server\Model\Money\CurrencyConverter;
use Psr\Http\Message\ServerRequestInterface;

class MarketController extends Controller {

    public function __construct(
        private readonly CurrencyConverter $converter,
        private readonly MarketIndex $index,
        private readonly MarketManager $manager
    ) {}

    public function averageCardPrices_v2(ServerRequestInterface $request): array {
        $currency = (new StringParam($request, "currency"))->value();
        $appids = (new ListParam($request, "appids"))->value();

        $appids = array_filter(
            array_map(fn($id) => intval($id), $appids),
            fn($id) => $id > 0
        );

        if (count($appids) == 0) {
            throw new InvalidValueException("appids");
        }

        $conversion = $this->converter->getConversion("USD", $currency);

        $this->index->recordRequest(...$appids);
        return $this->manager->getAverageCardPrices($appids, $conversion);
    }

    public function cards_v2(ServerRequestInterface $request): array {
        $currency = (new StringParam($request, "currency"))->value();
        $appid = (new IntParam($request, "appid"))->value();

        $conversion = $this->converter->getConversion("USD", $currency);

        $this->index->recordRequest($appid);
        return $this->manager->getCards($appid, $conversion);
    }
}
