<?php
include __DIR__ . '/../vendor/autoload.php';

use Formr\Formr;
use MathIKnow\Application;
use MathIKnow\ApplicationDatabase;
use MathIKnow\AvailabilityHelper;
use MathIKnow\GradeLevels;
use MathIKnow\MathCourses;
use MathIKnow\Template;
use MathIKnow\TimeHelper;
use MathIKnow\UserDatabase;
use MathIKnow\Utilities;

$_USER = UserDatabase::getUserFromBrowser();

if (!isset($_USER)) {
    Utilities::redirect("https://mathiknow.com/login");
}
if (!$_USER->isInGroup('tutor')) {
    Utilities::redirect("https://mathiknow.com/");
}

function verify_form(Formr $form) {
    global $_USER;

    $error = false;

    if (ApplicationDatabase::doesApplicationExist($_USER)) {
        $form->error_message("You can not submit this form more than once. If you need to update it, contact an admin.");
        $error = true;
    }

    $currentGrade = $form->post('current_grade', 'Current Grade', 'integer');
    if (!GradeLevels::doesGradeNumberExist($currentGrade) || $currentGrade < 9) {
        $form->error_message('Invalid grade');
        $error = true;
    }

    $mathCourses = [];
    foreach (MathCourses::getAllCourses() as $mathCourse) {
        if (isset($_POST[$mathCourse->prefixed_id])) {
            $mathCourses[] = $mathCourse;
        }
    }

    if (sizeof($mathCourses) < 1) {
        $form->error_message('Must select a course to tutor in');
        $error = true;
    }

    $fileInfo = $form->post('file');
    $fileName = $fileInfo ? $fileInfo['name'] : null;

    $availabilityPost = $form->post('availability');
    $availability = $availabilityPost ? AvailabilityHelper::getFromIdOrNameArray($availabilityPost) : [];

    $pastExperience = $form->post('past_experience','Past experience','max[1000]');

    if (!$form->errors() && !$error) {
        ApplicationDatabase::saveApplication(new Application(
            $_USER,
            GradeLevels::getLevelFromId($currentGrade),
            $mathCourses,
            $fileName,
            $availability,
            $pastExperience
        ));
        $form->success_message("Form submitted.");
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('MathIKnow - Tutor Application', ['/assets/css/tutorapp.css']);
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false, 'tutor-application');
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container">
            <div class="text-center">
                <h1 class="display-4 d-none d-lg-block">Tutor Application</h1>
                <h1 class="d-lg-none d-xl-none">Tutor Application</h1>
                <div class="card">
                    <div class="card-body">
                        <?php
                        $submittedApp = ApplicationDatabase::doesApplicationExist($_USER);
                        if (!$submittedApp) {
                            $form = new Formr('bootstrap');
                            $form->action = "./";

                            $form->upload_dir = '/var/www/html/uploads';
                            $form->upload_rename = 'hash';
                            $form->upload_accepted_types = 'pdf,docx,txt,zip';

                            if ($form->submitted()) {
                                verify_form($form);
                            }


                            echo $form->messages();
                            echo $form->form_open_multipart();
                            ?>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <?= $form->input_select('current_grade', 'Current Grade Level:', '', '',
                                        '', '', '',
                                        GradeLevels::getLevelsFromAssociative(9)) ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Courses that you are comfortable tutoring in:</label>
                                    <?php
                                    foreach (MathCourses::getAllCourses() as $course) {
                                        echo $form->input_checkbox($course->prefixed_id,$course->name,
                                            $course->prefixed_id,$course->prefixed_id);
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <?= $form->input_upload('file', 'Proof of Courses:');?>
                                    <p class="form-text text-muted text-left">
                                        If you need to send multiple files, send a zip file. Some forms of proof may include a screenshot of your grade portal, a picture of your unofficial transcript, past tutoring experiences and/or AP exam scores. We recommend submitting as many forms of proof as possible to prove your success in a subject. If you do not include any proof, your skill levels will be assessed prior to being accepted.
                                    </p>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <?php
                                    echo $form->input_select_multiple('availability[]','Availability:',
                                        '','availability','','','',
                                        AvailabilityHelper::getAvailabilitiesAssociative());
                                    ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <?=$form->input_textarea('past_experience', 'Past Tutoring or Extracurricular Math Experiences/Other Info:', '', '');?>
                                    <p class="form-text text-muted text-left">
                                        (if so add specifications, such as when, for what course/competition, etc)
                                    </p>
                                </div>
                            </div>
                            <div id="_submit" class="form-group">
                                <label class="sr-only" for="submit">Submit</label>
                                <input type="submit" name="submit" value="Submit" id="submit" class="btn btn-primary btn-block">
                            </div>
                            <?php
                            echo $form->form_close();
                            ?>
                            <?php
                        } else {
                            // SUBMITTED APP
                            $status = ApplicationDatabase::getStatus($_USER);
                            $statusHTML = "N/A";
                            if ($status->processing) {
                                $statusHTML = "Processing";
                            } else {
                                $statusHTML = "To be processed...";
                            }
                            if ($status->need_info) {
                                $statusHTML = "More information needed, please check your email.";
                            }
                            if ($status->deferred || $status->accepted) {
                                if ($status->accepted) {
                                    $statusHTML = '<div class="accepted">ACCEPTED</div>';
                                } else if ($status->deferred) {
                                    $statusHTML = '<div class="deferred">DEFERRED</div>';
                                }
                                $statusHTML .= " on " . TimeHelper::formatForUser($status->decision_date, $_USER);
                                if ($status->accepted) {
                                    $statusHTML .= "<br>The <a href='/tutor-portal'>tutor portal</a> is now available to you.";
                                }
                            }
                            ?>
                            <p>We have received your application. If you need to make changes to your application, please email us. Check back here regularly for status updates. We will reach out via email if more information is needed. <br> Please contact us if it has not begun processing within two weeks.</p>
                            <div>Status: <?=$statusHTML?></div>
                            <?php
                            if ($status->accepted) {
                                ?>
                            <script src="/assets/js/confetti.min.js"></script>
                            <script>
                            setInterval(function() {
                                confetti.start();
                            }, 1000);
                            </script>
                                <?php
                            }
                            ?>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    Template::footer();
    ?>
    <script>
        $('#availability option').mousedown(function(e) {
            e.preventDefault();
            $(this).prop('selected', !$(this).prop('selected'));
            return false;
        });
    </script>
</div>
</body>
</html>