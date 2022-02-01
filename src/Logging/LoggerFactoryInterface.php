<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Logging;

use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    public function createLogger(string $channel): LoggerInterface;
    public function createApiLogger(string $channel): LoggerInterface;
}
