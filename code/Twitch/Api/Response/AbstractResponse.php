<?php
namespace Twitch\Api\Response;

abstract class AbstractResponse {

    protected $headers;

    public function __construct(array $headers) {
        $this->headers = $headers;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function hasHeader(string $header): bool {
        return isset($this->headers[$header]);
    }

    public function getHeader(string $header): string {
        return $this->headers[$header];
    }
}
