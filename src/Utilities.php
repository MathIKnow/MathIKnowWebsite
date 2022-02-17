<?php

namespace MathIKnow;

class Utilities {
    public static function getIP() {
        if (array_key_exists('HTTP_CF_CONNECTING_IP', $_SERVER)) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        $keys = ['HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR', 'HTTP_CLIENT_IP'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                return $_SERVER[$key];
            }
        }
        return null;
    }

    public static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    public static function cutToLength($str, $length) {
        if (strlen($str) > $length) {
            return substr($str, 0, $length);
        }
        return $str;
    }

    public static function deleteCookie($cookie) {
        setcookie($cookie, '', TimeHelper::getUTCTimestamp() - 3600);
    }

    public static function redirect($url) {
        header("Location: $url");
        exit();
    }

    public static function capitalizeName($name) {
        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    public static function ellipsis($string, $length) {
        if (strlen($string) > $length) {
            return substr($string, 0, $length,) . '...';
        }
        return $string;
    }

    public static function isInt(string $string) {
        return preg_match("/^(-)?\d+$/m", $string);
    }

    public static function getVarDump($var) {
        ob_start();
        var_dump($var);
        return ob_get_clean();
    }
}