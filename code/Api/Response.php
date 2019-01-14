<?php
namespace Api;

class Response {

    public function setHeaders() {
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');
    }
}
