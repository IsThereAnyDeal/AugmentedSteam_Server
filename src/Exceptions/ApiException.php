<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Exceptions;

use League\Route\Http\Exception\BadRequestException;
use Psr\Http\Message\ResponseInterface;

abstract class ApiException extends BadRequestException {

    public function __construct(
        private readonly string $errorCode,
        private readonly string $errorMessage
    ) {
        parent::__construct();
    }

    public function buildJsonResponse(ResponseInterface $response): ResponseInterface {
        $this->headers['content-type'] = 'application/json';

        foreach ($this->headers as $key => $value) {
            /** @var ResponseInterface $response */
            $response = $response->withAddedHeader($key, $value);
        }

        if ($response->getBody()->isWritable()) {
            $response->getBody()->write(json_encode([
                "error" => $this->errorCode,
                "error_description" => $this->errorMessage,
                "status_code"   => $this->status,
                "reason_phrase" => $this->message
            ]));
        }

        return $response->withStatus($this->status, $this->message);
    }
}
