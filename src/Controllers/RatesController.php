<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Exceptions\InvalidValueException;
use AugmentedSteam\Server\Http\ListParam;
use AugmentedSteam\Server\Model\Money\CurrencyConverter;
use Psr\Http\Message\ServerRequestInterface;

class RatesController extends Controller {

    public function getRatesV1(ServerRequestInterface $request): array {
        $currencies = (new ListParam($request, "to"))->value();

        if (count($currencies) == 0) {
            throw new InvalidValueException("to");
        }

        $converter = new CurrencyConverter($this->db);
        return $converter->getAllConversionsTo($currencies);
    }
}
