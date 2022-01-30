<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Routing\Response;

use AugmentedSteam\Server\Exceptions\ApiException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use League\Route\Http;

class ApiResponseFactory implements ApiResponseFactoryInterface
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory) {
        $this->responseFactory = $responseFactory;
    }

    public function createSuccessResponse($data): ResponseInterface {
        $response = $this->responseFactory->createResponse()
            ->withAddedHeader('content-type', 'application/json');

        $response->getBody()->write(json_encode([
            "result" => "success",
            "data" => $data
        ]));

        return $response;
    }

    public function createErrorResponse(\Throwable $exception): ResponseInterface {
        $response = $this->responseFactory->createResponse()
            ->withAddedHeader('content-type', 'application/json');

        $error = "invalid_request";
        $errorDescription = "Invalid Request";

        if ($exception instanceof ApiException) {
            $error = $exception->getErrorCode();
            $errorDescription = $exception->getErrorMessage();
        }

        $status = ($exception instanceof Http\Exception)
            ? $exception->getStatusCode()
            : 500;

        $response->getBody()->write(json_encode([
            "result" => "error",
            "error" => $error,
            "error_description" => $errorDescription,
            "status_code" => $status,
            "reason_phrase" => $errorDescription // potentially $exception->getMessage(), but might give out too much info to public
        ]));

        return $response->withStatus($status, strtok($exception->getMessage(), "\n"));
    }
}
