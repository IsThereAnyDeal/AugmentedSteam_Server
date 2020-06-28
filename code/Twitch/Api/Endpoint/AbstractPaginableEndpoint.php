<?php
namespace Twitch\Api\Endpoint;

use GuzzleHttp\Client;
use Twitch\Api\Exception\TwitchApiException;
use Twitch\Api\Response\DataResponse;

abstract class AbstractPaginableEndpoint extends AbstractEndpoint {

    private $endpoint;
    private $method;

    private $cursor = null;
    private $reachedEnd = false;

    private $params = [];

    protected function __construct(Client $client, string $endpoint, string $method) {
        parent::__construct($client);
        $this->endpoint = $endpoint;
        $this->method = $method;
    }

    final protected function setParam(string $param, $value): void {
        $this->params[$param] = $value;
    }

    final protected function getParam(string $param) {
        return $this->params[$param];
    }

    private function getParams(): array {
        $params = $this->params;

        if (!is_null($this->cursor)) {
            $params['after'] = $this->cursor;
        }

        return $params;
    }

    public function setFirst(int $size) {
        $this->setParam("first", $size);
        return $this;
    }

    public function getNextPage(): array {

        if ($this->reachedEnd) {
            return [];
        }

        try {
            $response = $this->execute($this->endpoint, $this->getParams(), $this->method);
        } catch (TwitchApiException $e) {
            return [];
        }

        if (!($response instanceof DataResponse)) {
            return [];
        }

        if ($response->hasCursor()) {
            $this->cursor = $response->getCursor();
        } else {
            $this->reachedEnd = true;
        }

        return $response->getData();
    }

    public function getAllPages(): array {
        $result = [];
        $data = $this->getNextPage();

        while (!empty($data)) {
            $result = array_merge($result, $data);
            $data = $this->getNextPage();
        }
        return $result;
    }

    public function getPageEnumerator() {
        $data = $this->getNextPage();

        while (!empty($data)) {
            yield $data;
            $data = $this->getNextPage();
        }
    }

    public function getItemEnumerator(int $limit = -1) {
        $data = $this->getNextPage();

        $items = 0;
        while (!empty($data)) {
            foreach($data as $item) {
                yield $item;
                if ($limit > 0 && ++$items >= $limit) { break 2; }
            }
            $data = $this->getNextPage();
        }
    }
}
