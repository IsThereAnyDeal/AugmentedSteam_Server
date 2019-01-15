<?php
namespace Core;

use Config;
use dibi;
use Dibi\Exception;

class Database {

    private static $database = null;

    /**
     * @return \Dibi\Connection|null
     */
    public static function connect() {
        if (!is_null(self::$database)) {
            return self::$database;
        }

        try {
            self::$database = dibi::connect([
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

        return self::$database;
    }

}
