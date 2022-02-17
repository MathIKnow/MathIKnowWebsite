<?php

include __DIR__ . '/vendor/autoload.php';

use MathIKnow\Template;
use MathIKnow\UserDatabase;

$_USER = UserDatabase::getUserFromBrowser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('MathIKnow', ['/assets/css/home.css'])
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false, 'home');
        ?>
    </nav>
    <main class="flex-fill">
        <div class="home-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
            <h1 class="display-1 d-none d-lg-block">MathIKnow Tutoring</h1>
            <h1 class="d-lg-none d-xl-none">MathIKnow Tutoring</h1>
            <h2 class="font-weight-lighter">Free tutoring for middle to high school level mathematics</h2>
        </div>
        <div class="container main-container">
            <div class="text-center">
                <?php
                if (!isset($_USER)) {
                    ?>
                    <a href="/register" class="btn btn-secondary btn-lg active portal-button" role="button">Create an Account Today</a>
                    <?php
                } else {
                ?>
                    <?php
                    if ($_USER->isInGroup('tutor')) {
                    ?>
                    <a href="/tutor-application" class="btn btn-secondary btn-lg active portal-button" role="button">Tutor Application</a>
                    <?php
                    }
                    if ($_USER->isInGroup('tutor_approved')) {
                    ?>
                    <a href="/tutor-portal" class="btn btn-primary btn-lg active portal-button" role="button">Tutor Portal</a>
                    <?php
                    }
                    if ($_USER->isInGroup('student')) {
                    ?>
                    <a href="/student-portal" class="btn btn-dark btn-lg active portal-button" role="button">Student Portal</a>
                    <?php
                    }
                }
                ?>
            </div>
        </div>
    </main>
    <?php
    Template::footer();
    ?>
</body>
</html>