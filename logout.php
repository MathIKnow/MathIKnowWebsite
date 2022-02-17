<?php

include __DIR__ . '/vendor/autoload.php';

use MathIKnow\UserDatabase;
use MathIKnow\Utilities;

if (isset($_COOKIE['login_token'])) {
    UserDatabase::deleteToken($_COOKIE['login_token']);
}
Utilities::deleteCookie('login_token');
Utilities::redirect("https://mathiknow.com/");