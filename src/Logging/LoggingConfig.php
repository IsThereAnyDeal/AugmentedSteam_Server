<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Logging;

use Nette\Schema\Expect;
use Nette\Schema\Processor;

class LoggingConfig
{
    private readonly object $config;

    /**
     * @param array<string, mixed> $config
     */
    protected function __construct(array $config) {
        $this->config = (new Processor())->process(Expect::structure([
            "enabled" => Expect::bool(true),
            "maxFiles" => Expect::int(14)
        ]), $config);
    }

    public function isEnabled(): bool {
        return $this->config->enabled;
    }

    public function getMaxFiles(): int {
        return $this->config->maxFiles;
    }
}
