<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Config;

use Nette\Schema\Processor;
use Nette\Schema\Schema;

abstract class AConfig
{
    protected object $config;

    abstract protected function getSchema(): Schema;

    public function __construct(array $config) {
        $this->config = (new Processor())->process($this->getSchema(), $config);
    }

}
