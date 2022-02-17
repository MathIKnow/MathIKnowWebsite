<?php

include __DIR__ . '/../../vendor/autoload.php';

use MathIKnow\UserDatabase;
use MathIKnow\UserDiscordDatabase;
use MathIKnow\Utilities;

$_USER = UserDatabase::getUserFromBrowser();

if (!isset($_GET['code'])) {
    http_response_code(400);
    return;
}
$code = $_GET['code'];

$token = UserDiscordDatabase::getTokenFromCode($code);
UserDiscordDatabase::insertToken($_USER, $token);
$userObject = UserDiscordDatabase::getUserObject($token, $_USER);
$userObject->acceptInvite("https://discord.gg/zpy4ta3");
UserDiscordDatabase::updateDiscordData($userObject, $_USER);

Utilities::redirect("https://mathiknow.com/settings/link-discord/");