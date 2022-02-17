<?php
include __DIR__ . '/../../vendor/autoload.php';

use MathIKnow\Template;
use MathIKnow\UserDatabase;
use MathIKnow\UserDiscordDatabase;

$_USER = UserDatabase::getUserFromBrowser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('Link Discord');
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false);
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container">
            <div class="text-center">
                <h1 class="display-1 d-none d-lg-block">Link Discord</h1>
                <h1 class="d-lg-none d-xl-none">Link Discord</h1>
                <?php
                $token = UserDiscordDatabase::getUserToken($_USER);
                if (!$token) {
                    // NO TOKEN
                ?>
                <a href="<?= UserDiscordDatabase::getAuthorizationUrl()?>" class="btn btn-success btn-lg active" role="button">Login with Discord</a>
                <p>Note: Your Discord account will become a member of our Discord server if it is not already.</p>
                <?php
                } else {
                    // HAVE TOKEN
                    UserDiscordDatabase::updateDiscordData(UserDiscordDatabase::getUserObject($token, $_USER), $_USER);
                    $discordData = UserDiscordDatabase::getDiscordData($_USER);
                ?>
                <h2>Your Discord Account: <div class="text-monospace"><?=$discordData->getFullUsername()?></div></h2>
                <a href="<?= UserDiscordDatabase::getAuthorizationUrl()?>" class="btn btn-success btn-lg active" role="button">Relogin with Discord</a>
                <?php
                if ($_USER->isInGroup('student')) {
                ?>
                <a href="/student-portal/" class="btn btn-secondary btn-lg active" role="button">Student Portal</a>
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
</div>
</body>
</html>