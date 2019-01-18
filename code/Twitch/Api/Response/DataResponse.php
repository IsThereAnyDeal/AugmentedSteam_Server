<?php
namespace Twitch\Api\Response;

class DataResponse extends AbstractResponse {

    private $response;

    public function __construct(array $headers, array $response) {
        parent::__construct($headers);
        $this->response = $response;
    }

    public function getData(): array {
        return $this->response['data'];
    }

    public function hasCursor(): bool {
        return isset($this->response['pagination']['cursor']);
    }

    public function getCursor(): string {
        return $this->response['pagination']['cursor'];
    }

}
