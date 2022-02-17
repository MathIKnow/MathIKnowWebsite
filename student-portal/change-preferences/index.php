<?php
include __DIR__ . '/../../vendor/autoload.php';

use Formr\Formr;
use MathIKnow\MathCourses;
use MathIKnow\Template;
use MathIKnow\UserDatabase;
use MathIKnow\UserPreferences;
use MathIKnow\Utilities;

$_USER = UserDatabase::getUserFromBrowser();

if (!isset($_USER)) {
    Utilities::redirect("https://mathiknow.com/login");
}
if (!$_USER->isInGroup('student')) {
    Utilities::redirect("https://mathiknow.com/");
}

$validContactMethods = ['Email' => 'Email', 'Discord' => 'Discord'];

function verify_form(Formr $form) {
    global $_USER, $validContactMethods;

    /*$mathCourses = [];
    foreach (MathCourses::getAllCourses() as $mathCourse) {
        if (isset($_POST[$mathCourse->prefixed_id])) {
            $mathCourses[] = $mathCourse;
        }
    }

    if (!$form->errors()) {
        UserDatabase::setMathCourses($_USER, $mathCourses);
        $form->success_message("Updated courses.");
    }*/
    $contactMethod = $form->post('contact_method');
    if (!$contactMethod || !in_array($contactMethod, $validContactMethods)) {
        $form->error_message("Invalid contact method");
        return;
    }

    if (!$form->errors()) {
        $preferences = new UserPreferences($contactMethod);
        UserDatabase::setPreferences($_USER, $preferences);
        $form->success_message("Updated preferences.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('Change Preferences', ['/assets/css/tutorrequest.css']);
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
                'Student Portal' => '/student-portal',
                'Change Preferences'
            ]);
            ?>
            <div class="text-center">
                <div class="card">
                    <div class="card-body">
                        <?php
                        $form = new Formr('bootstrap');
                        $form->action = "./";

                        if ($form->submitted()) {
                            verify_form($form);
                        }

                        echo $form->messages();
                        echo $form->form_open();
                        ?>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <?php
                                /*$currentCourses = UserDatabase::getMathCourses($_USER);
                                foreach (MathCourses::getAllCourses() as $course) {
                                    $extraHTML = in_array($course, $currentCourses) ? 'checked=checked' : '';
                                    echo $form->input_checkbox($course->prefixed_id,$course->name,
                                        $course->prefixed_id,$course->prefixed_id, $extraHTML);
                                }*/
                                $currentPreferences = UserDatabase::getPreferences($_USER);
                                echo $form->input_select('contact_method', 'Preferred Contact Method',
                                    '', '', '', '',
                                    $currentPreferences->contactMethod,
                                    $validContactMethods)
                                ?>
                            </div>
                        </div>
                        <div id="_submit" class="form-group">
                            <label class="sr-only" for="submit">Update</label>
                            <input type="submit" name="submit" value="Update" id="submit" class="btn btn-primary btn-block">
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