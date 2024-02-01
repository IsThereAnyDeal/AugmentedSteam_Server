<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Endpoints;

use Nette\Schema\Expect;
use Nette\Schema\Processor;

class EndpointsConfig
{
    /**
     * @var object{
     *     wsgf: string,
     *     steamspy: string,
     *     steamrep: string,
     *     steampeek: string,
     *     itad: string
     * }
     */
    private readonly object $config;

    public function __construct(mixed $config) {
        // @phpstan-ignore-next-line
        $this->config = (new Processor())->process(Expect::structure([
            "wsgf" => Expect::string()->required(),
            "steamspy" => Expect::string()->required(),
            "steamrep" => Expect::string()->required(),
            "steampeek" => Expect::string()->required(),
            "itad" => Expect::string()->required()
        ]), $config);
    }

    public function getWSGFEndpoint(): string {
        return $this->config->wsgf;
    }

    public function getSteamSpyEndpoint(int $appid): string {
        return sprintf($this->config->steamspy, $appid);
    }

    public function getSteamRepEndpoint(): string {
        return $this->config->steamrep;
    }

    public function getSteamPeekEndpoint(): string {
        return $this->config->steampeek;
    }

    public function getIsThereAnyDealApiHost(): string {
        return $this->config->itad;
    }
}
