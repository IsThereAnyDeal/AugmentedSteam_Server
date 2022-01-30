<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing\Response;

use Psr\Http\Message\ResponseInterface;

interface ApiResponseFactoryInterface
{
    public function createSuccessResponse($data): ResponseInterface;
    public function createErrorResponse(\Throwable $exception): ResponseInterface;
}
