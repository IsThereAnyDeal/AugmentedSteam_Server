<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Http\Param;
use AugmentedSteam\Server\Model\Money\CurrencyConverter;
use League\Route\Http\Exception\BadRequestException;
use Psr\Http\Message\ServerRequestInterface;

class RatesController extends Controller {

    public function getRatesV1(ServerRequestInterface $request): array {
        $currencies = (new Param($request, "to"))
            ->list();

        if (count($currencies) == 0) {
            throw new BadRequestException();
        }

        $converter = new CurrencyConverter($this->db);
        return $converter->getAllConversionsTo($currencies);
    }
}
