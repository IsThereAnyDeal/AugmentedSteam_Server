<?php
namespace Api;

class Endpoint {

    private $params = [];

    private $body = null;

    public function __construct() {
        header("Access-Control-Allow-Origin: *"); // FIXME(tfedor) Access Control

        // optional
        if (!empty($_GET['optional'])) {
            $this->params['optional'] = array_flip(explode(",", $_GET['optional']));
        }
    }

    /**
     * Set up required and optional parameters for this endpoint
     * @param string[] $required        List of required parameter names that needs to be provided by user
     * @param mixed[]  $optional        Map of optional paramaters and their default values
     * @param string[] $requiredPost    List of required parameter names that needs to be provided by user via POST
     * @param mixed[]  $optionalPost    Map of optional paramaters and their default values provided via POST
     * @return $this
     */
    public function params($required=[], $optional=[], $requiredPost=[], $optionalPost=[]) {
        $this->parseRequiredParams($_GET, $required);
        $this->parseRequiredParams($_POST, $requiredPost);

        $this->parseOptionalParams($_GET, $optional);
        $this->parseOptionalParams($_POST, $optionalPost);
        return $this;
    }

    private function parseRequiredParams($request, $required) {
        foreach($required as $p) {
            if (!isset($request[$p]) || trim($request[$p]) == "") {
                (new Response())->fail("missing_params", "Required parameter '$p' is missing");
            }
            $this->params[$p] = trim($request[$p]);
        }
    }

    private function parseOptionalParams($request, $optional) {
        foreach($optional as $p => $value) {
            $this->params[$p] = $value;
            if (isset($request[$p]) && trim($request[$p]) != "") {
                $this->params[$p] = trim($request[$p]);
            }
        }
    }

    public function hasOptional($key) {
        return isset($this->params['optional']) && array_key_exists($key, $this->params['optional']);
    }

    public function getParam($key) {
        return $this->params[$key];
    }

    public function getParamAsArray(string $key, string $delimiter=","): array {
        return isset($this->params[$key]) && is_string($this->params[$key])
            ? explode($delimiter, $this->params[$key])
            : [];
    }

    public function getParamAsInt(string $key): int {
        return empty($this->params[$key]) ? 0 : (int)$this->params[$key];
    }

    public function jsonBody() {
        $json = file_get_contents("php://input");
        if (empty($json)) {
            (new Response())->fail("missing_body", "Required body of the message is missing");
        }

        $data = json_decode($json, true);
        if ($data === false) {
            (new Response())->fail("invalid_json", "Required body of the message is not a valid JSON");
        }

        $this->body = $data;
        return $this;
    }

    public function getBody() {
        return $this->body;
    }
}
