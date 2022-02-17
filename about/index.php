<?php
include __DIR__ . '/../vendor/autoload.php';

use MathIKnow\Template;
use MathIKnow\UserDatabase;

$_USER = UserDatabase::getUserFromBrowser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('MathIKnow - About');
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false, 'about');
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container">
            <div class="text-center">
                <div class="jumbotron">
                    <h1 class="display-4">About</h1>
                    <p class="lead">MathIKnow is a free online Math tutoring service founded by Andrew Tran and He Yang with Owen Smith as a rigorous tester in the fall of 2020. During this time, the COVID-19 pandemic has overtaken the world, which has shifted many students into the unfamiliar environment of virtual learning. While this is a safer option than returning to school, it is similar to the limited resources students normally have access to, like in school or after school tutoring, along with the challenges students face when it comes to being able to effectively learn online verses the normal classroom atmosphere they are used to. This was more of an issue for lower class families, as they were not able to afford regular tutoring for courses such as math. With this knowledge, He Yang, a fellow Mathlete and tutor, along with Andrew Tran, also a Mathlete and a self-taught developer, created the website MathIKnow.</p>
                    <p class="lead">MathIKnow was made with the global community in mind with code that automatically displays dates in user's timezones so avoid confusion.</p>
                    <p class="lead">We hope that this site serves as a service to our communities allowing for students who need math help to get it regardless of financial ability or environment.</p>
                    <p class="lead">This website was submitted to Congressional App Challenge 2020 under MN-05 (Ilhan Omar).</p>
                </div>
                <div class="jumbotron">
                    <h1 class="display-4">Contact</h1>
                    <p class="lead">Andrew Tran <a href="mailto:andrew.tran@mathiknow.com">andrew.tran@mathiknow.com</a></p>
                    <p class="lead">He Yang <a href="mailto:he.yang@mathiknow.com">he.yang@mathiknow.com</a></p>
                    <p class="lead">Owen Smith <a href="mailto:owen_smith22@rdale.org">owen_smith22@rdale.org</a></p>
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