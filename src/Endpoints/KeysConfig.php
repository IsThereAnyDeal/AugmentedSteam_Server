<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Endpoints;

use Nette\Schema\Expect;
use Nette\Schema\Processor;

class KeysConfig
{
    /**
     * @var object{
     *    itad: string,
     *    steampeek: string
     * }
     */
    private readonly object $config;

    public function __construct(mixed $config) {
        // @phpstan-ignore-next-line
        $this->config = (new Processor())->process(Expect::structure([
            "itad" => Expect::string()->required(),
            "steampeek" => Expect::string()->required()
        ]), $config);
    }

    public function getIsThereAnyDealApiKey(): string {
        return $this->config->itad;
    }

    public function getSteamPeekApiKey(): string {
        return $this->config->steampeek;
    }
}
