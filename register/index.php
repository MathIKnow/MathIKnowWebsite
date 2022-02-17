<?php
include __DIR__ . '/../vendor/autoload.php';

use Formr\Formr;
use MathIKnow\CountryStateHelper;
use MathIKnow\Template;
use MathIKnow\TimeHelper;
use MathIKnow\UserDatabase;
use MathIKnow\Utilities;

$_USER = UserDatabase::getUserFromBrowser();

if (isset($_USER)) {
    Utilities::redirect("https://mathiknow.com/");
}

function verify_form(Formr $form) {
    $error = false;


    if (!isset($_POST['g-recaptcha-response'])){
        $form->error_message('Missing captcha');
        return;
    }
    $captcha = $_POST['g-recaptcha-response'];
    $secretKey = "";
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
    $response = file_get_contents($url);
    $responseKeys = json_decode($response,true);
    // should return JSON with success as true
    if(!$responseKeys["success"]) {
        $form->error_message('Invalid captcha');
        return;
    }

    $form->post('tos', 'Please agree that you are over 13 years old and agree to the Terms of Service.');
    $username = $form->post('username','Username','min_length[6]|max_length[32]|alpha_dash');
    if (UserDatabase::doesUsernameExist($username)) {
        $form->error_message('Username is already in use');
        $error = true;
    }
    $first_name = $form->post('first_name', 'First name', 'min_length[1]|max_length[50]|alpha');
    $last_name = $form->post('last_name', 'Last name', 'min_length[1]|max_length[50]|alpha');
    $email = $form->post('email', 'Email', 'valid_email|sanitize_email');
    $form->post('confirm_email', 'Email Confirmation', 'matches[email]');
    if (UserDatabase::doesEmailExist($email)) {
        $form->error_message('Email is already in use');
        $error = true;
    }
    $password = $form->post('password','Password','min_length[8]|max_length[64]');
    $form->post('confirm_password', 'Password Confirmation', 'matches[password]');

    // Country & Region validation
    $countryCode = $form->post('geo_country', 'Country');
    $regionCode = '';
    if (!in_array($countryCode, CountryStateHelper::getCountryISO3List())) {
        $form->error_message('Invalid country');
        $error = true;
    } else {
        $validRegionCodes = CountryStateHelper::getStateCodes($countryCode);
        if (sizeof($validRegionCodes) > 0) {
            $regionCode = $form->post('geo_region', 'Region');
            if (!in_array($regionCode, $validRegionCodes)) {
                $form->error_message('Invalid region');
                $error = true;
            }
        }
    }
    if ($regionCode == ' ') {
        $regionCode = '';
    }

    // Timezone validation
    $timezone = $form->post('timezone', 'Timezone');
    $timezoneOffset = '';
    if (!in_array($timezone, TimeHelper::getTimezoneIdentifiers())) {
        $form->error_message('Invalid timezone');
        $error = true;
    } else {
        $timezoneOffset = TimeHelper::getTimezoneOffsetSecondsFromIdentifier($timezone);
    }

    $studentTutorOption = $form->post('role', 'Role');
    if (!in_array($studentTutorOption, ['student', 'tutor', 'both'])) {
        $form->error_message('Invalid role');
        $error = true;
    }
    $isStudent = false;
    $isTutor = false;
    switch ($studentTutorOption) {
        case 'student':
            $isStudent = true;
            break;
        case 'tutor':
            $isTutor = true;
            break;
        case 'both':
            $isStudent = true;
            $isTutor = true;
            break;
    }

    if (!$form->errors() && !$error) {
        $passwordHash = UserDatabase::hashPassword($password);

        $id = UserDatabase::createUser([
            'email' => $email,
            'password' => $passwordHash,
            'username' => $username,
            'first_name' => Utilities::capitalizeName($first_name),
            'last_name' => Utilities::capitalizeName($last_name),
            'signup_ip' => Utilities::getIP(),
            'signup_date' => TimeHelper::getUTCTimestamp(),
            'geo_region' => $regionCode,
            'geo_country' => $countryCode,
            'timezone' => $timezone,
            'timezone_offset' => $timezoneOffset
        ]);

        UserDatabase::updateGroups($id, $isStudent, $isTutor);
        UserDatabase::sendConfirmationEmail($id, $email);

        $form->success_message("Your account has been successfully created. Please verify by clicking the link in the verification email that has just been sent to $email. The email may take up to 30 minutes to arrive. If you would like for it to be resent, go to the login page and login.");
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('MathIKnow - Register');
    ?>
    <script src='https://www.google.com/recaptcha/api.js' async defer></script>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER);
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container">
            <div class="text-center">
                <h1 class="display-4 d-none d-lg-block">Create an Account</h1>
                <h1 class="d-lg-none d-xl-none">Create an Account</h1>
                <div class="card">
                    <div class="card-body">
                        <?php
                        $form = new Formr('bootstrap');
                        $form->action = "./";
                        $form->required = '(geo_region)';

                        if ($form->submitted()) {
                            verify_form($form);
                        }

                        echo $form->messages();
                        echo $form->form_open();
                        ?>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <?= $form->input_text('username', 'Username') ?>
                                <p class="form-text text-muted text-left">
                                    Your username will be shared with other tutors and students.
                                </p>
                            </div>
                            <div class="form-group col-md-3">
                                <?= $form->input_text('first_name', 'First Name') ?>
                                <p class="form-text text-muted text-left">
                                    Your real name will not be shared.
                                </p>
                            </div>
                            <div class="form-group col-md-3">
                                <?= $form->input_text('last_name', 'Last Name') ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <?=$form->input_email('email', 'Email Address')?>
                                <p class="form-text text-muted text-left">
                                    It is recommended that you use a personal email (like one from gmail) as school email servers often block external emails. This will also ensure access to your account after you graduate or if you switch schools.
                                </p>
                            </div>
                            <div class="form-group col-md-6">
                                <?= $form->input_email('confirm_email', 'Confirm Email Address') ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <?= $form->input_password('password', 'Password') ?>
                                <p class="form-text text-muted text-left">
                                    Must be at least 8 characters
                                </p>
                            </div>
                            <div class="form-group col-md-6">
                                <?= $form->input_password('confirm_password', 'Confirm Password') ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <?= $form->input_select('geo_country', 'Country', '','','',
                                    '','USA', CountryStateHelper::getISO3ToCountryMapping()) ?>
                            </div>
                            <div class="form-group col-md-6">
                                <?= $form->input_select('geo_region', 'State/Province/Region', '', '',
                                    '', '', ' ', [])?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <?= $form->input_select('timezone', 'Timezone', '', '', '',
                                    '', '', TimeHelper::getTimezoneDropdown())?>
                            </div>
                            <div class="form-group col-md-6">
                                <?= $form->input_select('role', 'I want to be a', '', '',
                                    '', '', 'student',
                                    ['student' => 'Student', 'tutor' => 'Tutor', 'both' => 'Student and Tutor'])?>
                                <p class="form-text text-muted text-left">
                                    Anyone can become a student. Becoming a tutor involves an application process. <br/>
                                    If you would like to both learn and teach, you may become both a student and a tutor.
                                </p>

                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <?= $form->input_checkbox('tos', 'I agree that I am at least 13 years of age and agree to the <a href="/terms-of-service">Terms of Service</a>.', 'tos')?>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="g-recaptcha" data-sitekey="6Leh0dgZAAAAAAAdDNvZDOJfu-C3ILn01o4YuX8m"></div>
                            </div>
                        </div>
                        <div id="_submit" class="form-group">
                            <label class="sr-only" for="submit">Submit</label>
                            <input type="submit" name="submit" value="Submit" id="submit" class="btn btn-primary btn-block">
                        </div>
                        <?php
                        echo $form->form_close();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    Template::footer(['//cdnjs.cloudflare.com/ajax/libs/jstimezonedetect/1.0.4/jstz.min.js', '/assets/js/register.js']);
    ?>
</div>
</body>
</html>