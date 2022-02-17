<?php

include __DIR__ . '/../vendor/autoload.php';

use MathIKnow\TutorRequestDatabase;
use MathIKnow\UserDatabase;

$_USER = UserDatabase::getUserFromBrowser();

if (!$_USER || !isset($_POST['json'])) {
    http_response_code(403);
    return;
}

/*function error($message) {
    ?>
    <div class="alert alert-danger" role="alert"><?=$message?></div>
    <?php
    exit();
}*/

$jsonString = $_POST['json'];

$schedules = TutorRequestDatabase::parseScheduleJSON($jsonString);

foreach ($schedules as $schedule) {
    echo $schedule->toHTML('new-tutoring-request', $_USER);
}

//echo "<pre>"; var_dump($json); echo "</pre>";