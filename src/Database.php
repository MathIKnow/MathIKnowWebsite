<?php

namespace MathIKnow;

use DB;

class Database {
    public static $DELIMITER = "<>";

    public static function connect() {
        DB::$host = 'localhost';
        DB::$port = '3306';
        DB::$user = 'website-prod';
        DB::$password = 'NOT REAL PASSWORD ANYMORE';
        DB::$dbName = DB::$user;
    }

    public static function joinList(array $array) : string {
        foreach ($array as $key => $value) {
            $array[$key] = urlencode($value);
        }
        return implode(self::$DELIMITER, $array);
    }

    public static function expandList(string $string) : array {
        $array = explode(self::$DELIMITER, $string);
        foreach ($array as $key => $value) {
            $array[$key] = urldecode($value);
        }
        return $array;
    }
}
