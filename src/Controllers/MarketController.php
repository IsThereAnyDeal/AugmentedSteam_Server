<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Managers\Market\MarketIndex;
use AugmentedSteam\Server\Data\Managers\Market\MarketManager;
use AugmentedSteam\Server\Exceptions\InvalidValueException;
use AugmentedSteam\Server\Lib\Http\IntParam;
use AugmentedSteam\Server\Lib\Http\ListParam;
use AugmentedSteam\Server\Lib\Http\StringParam;
use AugmentedSteam\Server\Lib\Money\CurrencyConverter;
use Psr\Http\Message\ServerRequestInterface;

class MarketController extends Controller {

    public function __construct(
        private readonly CurrencyConverter $converter,
        private readonly MarketIndex $index,
        private readonly MarketManager $manager
    ) {}

    /**
     * @return array<mixed>
     */
    public function averageCardPrices_v2(ServerRequestInterface $request): array {
        /** @var string $currency */
        $currency = (new StringParam($request, "currency"))->value();

        /** @var list<string> $appids */
        $appids = (new ListParam($request, "appids"))->value();

        $appids = array_values(array_filter(
            array_map(fn($id) => intval($id), $appids),
            fn($id) => $id > 0
        ));

        if (count($appids) == 0) {
            throw new InvalidValueException("appids");
        }

        $conversion = $this->converter->getConversion("USD", $currency);
        if (!is_float($conversion)) {
            throw new \Exception();
        }

        $this->index->recordRequest(...$appids);
        return $this->manager->getAverageCardPrices($appids, $conversion);
    }

    /**
     * @return array<mixed>
     */
    public function cards_v2(ServerRequestInterface $request): array {
        /** @var string $currency */
        $currency = (new StringParam($request, "currency"))->value();

        /** @var int $appid */
        $appid = (new IntParam($request, "appid"))->value();

        $conversion = $this->converter->getConversion("USD", $currency);
        if (!is_float($conversion)) {
            throw new \Exception();
        }

        $this->index->recordRequest($appid);
        return $this->manager->getCards($appid, $conversion);
    }
}
