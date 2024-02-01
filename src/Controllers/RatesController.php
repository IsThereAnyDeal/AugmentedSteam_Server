<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Exceptions\InvalidValueException;
use AugmentedSteam\Server\Lib\Http\ListParam;
use AugmentedSteam\Server\Lib\Money\CurrencyConverter;
use Psr\Http\Message\ServerRequestInterface;

class RatesController extends Controller {

    public function __construct(
        private readonly CurrencyConverter $converter
    ) {}

    public function rates_v1(ServerRequestInterface $request): array {
        $currencies = (new ListParam($request, "to"))->value();

        if (count($currencies) == 0 || count($currencies) > 2) {
            throw new InvalidValueException("to");
        }

        return $this->converter->getAllConversionsTo($currencies);
    }
}
