<?php
include __DIR__ . '/../vendor/autoload.php';

use MathIKnow\TutorRequestDatabase;
use MathIKnow\UserDatabase;
use MathIKnow\Utilities;

$_USER = UserDatabase::getUserFromBrowser();

if (!isset($_USER) || !isset($_POST['request_id']) || !isset($_POST['action']) ||!isset($_POST['checked']) || !$_USER->isInGroup('tutor_approved')) {
    http_response_code(403);
    return;
}

$requestId = $_POST['request_id'];
if (!Utilities::isInt($requestId)) {
    http_response_code(403);
    return;
}

$checked = filter_var($_POST['checked'], FILTER_VALIDATE_BOOLEAN);
$action = strtolower($_POST['action']);

if ($action === 'reached_out') {
    TutorRequestDatabase::updateReachedOut($requestId, $_USER, $checked);
} else if ($action === 'claimed') {
    TutorRequestDatabase::updateClaimed($requestId, $_USER, $checked);
} else {
    http_response_code(400);
}