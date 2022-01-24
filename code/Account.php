<?php
class Account {

    public static function login() {

        $endpoint = new \Api\Endpoint();
        $endpoint->params([], [], ["steam_id"], []);

        if (!array_key_exists("session_id", $_COOKIE)) { self::redirect(); }

        $result = \dibi::fetch("SELECT * FROM [session_ids] WHERE [session_id] = %bin", hex2bin($_COOKIE["session_id"]));

        if (is_null($result) || $result["steam_id"] !== $endpoint->getParam("steam_id") || strtotime($result["expiry"]) < time()) {
            self::redirect();
        }

        return $result["steam_id"];
    }

    private static function redirect() {
        (new \Api\Response())->fail("login_required", "Login required", 401);
    }
}
