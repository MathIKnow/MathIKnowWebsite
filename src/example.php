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
    Template::head('MathIKnow - <@@@@@@@@>', ['/assets/css/<@@@@@@@@>.css']);
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false, '<@@@NAVBAR PAGE NAME@@@>');
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container">
            <div class="text-center">

            </div>
        </div>
    </main>
    <?php
    Template::footer();
    ?>
</div>
</body>
</html>