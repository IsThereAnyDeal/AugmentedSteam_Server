<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\Logging;

use Nette\Schema\Expect;
use Nette\Schema\Processor;

class LoggingConfig
{
    private readonly object $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config) {
        $this->config = (new Processor())->process(Expect::structure([
            "enabled" => Expect::bool(true)
        ]), $config);
    }

    public function isEnabled(): bool {
        return $this->config->enabled;
    }
}
