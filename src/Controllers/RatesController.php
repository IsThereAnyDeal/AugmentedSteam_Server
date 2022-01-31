<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Exceptions\MissingParameterException;
use AugmentedSteam\Server\Model\Money\CurrencyConverter;
use Psr\Http\Message\ServerRequestInterface;

class RatesController extends Controller {

    public function getRatesV1(ServerRequestInterface $request): array {
        $query = $request->getQueryParams();
        if (empty($query['to'])) {
            throw new MissingParameterException("to");
        }

        $currencies = explode(",", $query['to']);

        $converter = new CurrencyConverter($this->db);
        return $converter->getAllConversionsTo($currencies);
    }
}
