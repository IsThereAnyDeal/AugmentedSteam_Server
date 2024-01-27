<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Logging;

use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    public function logger(string $channel): LoggerInterface;
    public function access(): LoggerInterface;
}
