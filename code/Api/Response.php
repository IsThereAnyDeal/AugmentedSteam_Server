<?php
namespace Api;

class Response {

    /**
     * @deprecated Used only as a intermediate step until all API endpoints are converted to send api
     */
    public function setHeaders() {
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');
    }

    private $data = [];

    public function data(array $data): Response {
        $this->data = [
            "data" => $data
        ];
        return $this;
    }

    public function respond() {
        \Log::channel("api", true)->info(
            "Response",
            [
                "endpoint" => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
                "get" => $_GET,
                "post" => $_POST
            ]
        );

        $this->send("success", $this->data);
    }

    public function fail($errorCode="invalid_request", $errorMessage="Invalid request", $httpCode=400) {
        \Log::channel("api", true)->notice($_SERVER['REQUEST_URI'], [$errorCode => $errorMessage]);

        switch($httpCode) {
            case 400: header("HTTP/1.1 400 Bad Request"); break;
            case 500: header("HTTP/1.1 500 Internal Server Error"); break;
        }

        $this->send("error", ["error" => $errorCode, "error_description" => $errorMessage]);
    }

    private function send(string $result, array $data) {
        $pretty = isset($_GET['pretty']);

        header("Content-type: application/json");

        $data = array_merge(["result" => $result], $data);
        echo json_encode($data, ($pretty ? JSON_PRETTY_PRINT : 0));
        die();
    }
}
