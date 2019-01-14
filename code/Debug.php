<?php

class Debug {

    public static function _($var)  {
        echo "<pre>"; print_r($var); echo "</pre>";
    }

    public static function __($var) {
        echo "<pre>"; var_dump($var); echo "</pre>";
    }

}
