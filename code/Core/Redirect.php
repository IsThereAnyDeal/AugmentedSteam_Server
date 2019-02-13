<?php
namespace Core;

class Redirect {

    public static function to($where) {
        Header("Location: $where");
        die();
    }
}
