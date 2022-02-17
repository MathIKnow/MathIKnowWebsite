<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a id="navbar-logo" class="navbar-brand text-light" href="/">MathIKnow Tutoring</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
            <?php
            $divider = false;
            if (isset($_USER)) {
                if ($_USER->isInGroup('tutor')) {
                    $divider = true;
            ?>
            <li class="nav-item">
                <a class="nav-link<?=$active == 'tutor-application' ? ' active' : ''?>" href="/tutor-application">Tutor Application</a>
            </li>
            <?php
                }
                if ($_USER->isInGroup('tutor_approved')) {
                    $divider = true;
            ?>
            <li class="nav-item">
                <a class="nav-link<?=$active == 'tutor-portal' ? ' active' : ''?>" href="/tutor-portal">Tutor Portal</a>
            </li>
            <?php
                }
            ?>
            <?php
                if ($_USER->isInGroup('student')) {
                    $divider = true;
            ?>
            <li class="nav-item">
                <a class="nav-link<?=$active == 'student-portal' ? ' active' : ''?>" href="/student-portal">Student Portal</a>
            </li>
            <?php
               }
                if ($_USER->isInGroup('admin')) {
                    $divider = true;
            ?>
            <li class="nav-item">
                <a class="nav-link<?=$active == 'admin-portal' ? ' admin' : ''?>" href="/admin-portal">Admin Portal</a>
            </li>
            <?php
              }
            ?>
            <?php
            }
            if ($divider) {
                ?>
            <li class="nav-item">
                <a class="nav-link disabled active">  |  </a>
            </li>
                <?php
            }
            ?>
            <li class="nav-item">
                <a class="nav-link<?=$active == 'home' ? ' active' : ''?>" href="/">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="//forums.mathiknow.com">Forums</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="//discord.gg/2aa6Gvn" target="_blank">Discord Chat</a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?=$active == 'admin-portal' ? ' gallery' : ''?>" href="/gallery/">Gallery</a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?=$active == 'admin-portal' ? ' about' : ''?>" href="/about/">About</a>
            </li>
        </ul>
        <?php
        if (!$hideTopRight) {
            if (!isset($_USER)) {
                ?>
            <div class="btn-group" role="group" aria-label="Account Buttons">
                <a class="btn btn-outline btn-info registration-button" href="/login">Login</a>
                <a class="btn btn-outline btn-secondary registration-button" href="/register">Register</a>
            </div>
            <?php
            } else {
            ?>
            <ul class="nav navbar-nav navbar-right">
                <li class="nav-item dropdown active">
                    <a class="nav-link dropdown-toggle" href="#" id="navbar-drop" data-toggle="dropdown">
                        <?= $_USER->username ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="/logout.php">Logout</a>
                    </div>
                </li>
            </ul>
            <?php
            }
            }
        ?>
    </div>
</nav>