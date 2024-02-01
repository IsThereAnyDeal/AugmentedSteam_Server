<?php
namespace AugmentedSteam\Server\Controllers;

use AugmentedSteam\Server\Data\Interfaces\PricesProviderInterface;
use JsonSerializable;
use League\Route\Http\Exception\BadRequestException;
use Psr\Http\Message\ServerRequestInterface;

class PricesController extends Controller {

    public function __construct(
        private readonly PricesProviderInterface $pricesProvider
    ) {}

    /**
     * @return array<mixed>|JsonSerializable
     */
    public function prices_v2(ServerRequestInterface $request): array|JsonSerializable {
        $data = $request->getBody()->getContents();
        if (!json_validate($data)) {
            throw new BadRequestException();
        }

        $params = json_decode($data, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($params) || !isset($params['country']) || !is_string($params['country'])) {
            throw new BadRequestException();
        }

        $country = $params['country'];
        $shops = $this->validateIntList($params, "shops");
        $apps = $this->validateIntList($params, "apps");
        $subs = $this->validateIntList($params, "subs");
        $bundles = $this->validateIntList($params, "bundles");
        $voucher = filter_var($params['voucher'] ?? true, FILTER_VALIDATE_BOOLEAN); // TODO unused right now

        $ids = array_merge(
            array_map(fn($id) => "app/$id", array_filter($apps)),
            array_map(fn($id) => "sub/$id", array_filter($subs)),
            array_map(fn($id) => "bundle/$id", array_filter($bundles)),
        );

        if (count($ids) == 0) {
            throw new BadRequestException();
        }

        $overview = $this->pricesProvider->fetch($ids, $shops, $country);
        return $overview ?? [];
    }
}
