<?php
namespace Core;

use Config;
use dibi;
use Dibi\Exception;

class Database {

    private static $loaded = false;

    public static function connect() {
        if (self::$loaded) {
            return;
        }

        try {
            dibi::connect([
                'driver'   => Config::DatabaseDriver,
                'host'     => Config::DatabaseHost,
                'username' => Config::DatabaseUser,
                'password' => Config::DatabasePassword,
                'database' => Config::DatabaseName,
                'charset'  => 'utf8',
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            die($e->getMessage());
        }

        self::$loaded = true;
    }

}
