<?php
include __DIR__ . '/../vendor/autoload.php';

use MathIKnow\TutorRequestDatabase;
use MathIKnow\UserDatabase;
use MathIKnow\Utilities;

$_USER = UserDatabase::getUserFromBrowser();

if (!isset($_USER) || !isset($_GET['request_id'])) {
    http_response_code(403);
    return;
}

$requestId = $_GET['request_id'];
if (!Utilities::isInt($requestId)) {
    http_response_code(403);
    return;
}

$request = TutorRequestDatabase::getRequestById($requestId);
if (!$request || $request->user->id !== $_USER->id) {
    http_response_code(403);
    echo "You do not have permission to delete this tutor request.";
    return;
}

TutorRequestDatabase::archiveRequest($requestId);
Utilities::redirect("https://mathiknow.com/student-portal/");



