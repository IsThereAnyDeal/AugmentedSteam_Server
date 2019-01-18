<?php
namespace Twitch\Api\Response;

class ErrorResponse extends AbstractResponse {

    private $response;

    public function __construct(array $headers, array $response) {
        parent::__construct($headers);
        $this->response = $response;
    }

    public function getError(): string {
        return $this->response['error'];
    }

    public function getStatus(): int {
        return $this->response['status'];
    }

    public function getMessage(): string {
        return $this->response['message'];
    }
}
