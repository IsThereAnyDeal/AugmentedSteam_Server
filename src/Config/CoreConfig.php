<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Config;

use Nette\Schema\Expect;
use Nette\Schema\Processor;

class CoreConfig
{
    private object $config;

    /** @param array<string, mixed> $config */
    public function __construct(array $config) {
        $this->config = (new Processor())->process(Expect::structure([
            "host" => Expect::string()->required(),
            "dev" => Expect::bool(false),
            "prettyErrors" => Expect::bool(false),
            "sentry" => Expect::structure([
                "enabled" => Expect::bool(false),
                "dsn" => Expect::string(),
                "environment" => Expect::string()
            ])
        ]), $config);
    }

    public function getHost(): string {
        return $this->config->host;
    }

    public function getEnvironment(): string {
        return $this->config->env;
    }

    public function isDev(): bool {
        return $this->config->dev;
    }

    public function isProduction(): bool {
        return !$this->isDev();
    }

    public function usePrettyErrors(): bool {
        return $this->config->prettyErrors;
    }

    public function isSentryEnabled(): bool {
        return $this->config->sentry->enabled;
    }

    public function getSentryDsn(): ?string {
        return $this->config->sentry->dsn;
    }

    public function getSentryEnvironment(): string {
        return $this->config->sentry->environment;
    }
}
