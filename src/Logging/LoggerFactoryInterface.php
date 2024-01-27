<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Logging;

use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    public function create(string $channel): LoggerInterface;
}
