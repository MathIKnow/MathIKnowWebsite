<?php
include __DIR__ . '/../vendor/autoload.php';

use MathIKnow\Template;
use MathIKnow\TutorRequestDatabase;
use MathIKnow\UserDatabase;
use MathIKnow\Utilities;

$_USER = UserDatabase::getUserFromBrowser();

if (!isset($_USER)) {
    Utilities::redirect("https://mathiknow.com/login");
}
if (!$_USER->isInGroup('student')) {
    Utilities::redirect("https://mathiknow.com/");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('MathIKnow - Student Portal');
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false, 'student-portal');
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container">
            <?php
            Template::breadcrumb([
                'Home' => '/',
                'Student Portal'
            ]);
            ?>
            <div class="text-center">
                <h2 class="display-4 d-none d-lg-block">Student Portal</h2>
                <h2 class="d-lg-none d-xl-none">Student Portal</h2>
                <div class="card">
                    <div class="card-body">
                        <div class="text-left">
                            <a href="new-tutoring-request" class="btn btn-primary btn-lg active portal-button" role="button">Request a Tutoring Session</a>
                            <a href="/settings/link-discord/" class="btn btn-success btn-lg active portal-button" role="button">Link Discord</a>
                            <a href="change-courses" class="btn btn-secondary btn-lg active portal-button" role="button">Change Math Courses</a>
                            <a href="change-preferences" class="btn btn-secondary btn-lg active portal-button" role="button">Change Preferences</a>
                        </div>
                        <br>
                        <br>
                        <h3>My Tutoring Requests:</h3>
                        <div id="tutoring-requests" class="text-left">
                            <?php
                            $requests = TutorRequestDatabase::getRequests($_USER);
                            foreach ($requests as $request) {
                                $request->echoStudentPortalHTML($_USER);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    Template::footer();
    ?>
</div>
</body>
</html>