<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Data\Updaters\Exfgls;

use Nette\Schema\Expect;
use Nette\Schema\Processor;

class ExfglsConfig
{
    /**
     * @var object{
     *     enabled: bool,
     *     bin: string,
     *     user: string,
     *     password: string
     * }
     */
    private object $config;

    public function __construct(mixed $config) {
        // @phpstan-ignore-next-line
        $this->config = (new Processor())->process(Expect::structure([
            "enabled" => Expect::bool(false),
            "bin" => Expect::string(),
            "user" => Expect::string(),
            "password" => Expect::string(),
        ]), $config);
    }

    public function isEnabled(): bool {
        return $this->config->enabled;
    }

    public function getBin(): string {
        return $this->config->bin;
    }

    public function getUser(): string {
        return $this->config->user;
    }

    public function getPassword(): string {
        return $this->config->password;
    }
}
