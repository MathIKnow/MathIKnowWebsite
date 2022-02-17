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
    Template::head('MathIKnow - Gallery');
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false, 'gallery');
        ?>
    </nav>
    <main class="flex-fill">
        <h1 class="display-4 d-none d-lg-block">Gallery</h1>
        <br>
        <img src="/gallery/img/1.png" style="margin-left: 20px;"/>
        <br>
        <img src="/gallery/img/2.png" style="margin-left: 20px;"/>
        <br>
        <img src="/gallery/img/3.png" style="margin-left: 20px;"/>
        <br>
        <img src="/gallery/img/4.png" style="margin-left: 20px;"/>
        <br>
        <img src="/gallery/img/5.png" style="margin-left: 20px;"/>
    </main>
    <?php
    Template::footer();
    ?>
</div>
</body>
</html>