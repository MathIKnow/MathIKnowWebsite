<?php
include __DIR__ . '/../../vendor/autoload.php';

use Formr\Formr;
use MathIKnow\DaysOfWeek;
use MathIKnow\FlexibleSchedule;
use MathIKnow\MathCourses;
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

$userMathCourses = UserDatabase::getMathCourses($_USER);

function verify_form(Formr $form) {
    global $_USER;

    $name = $form->post('name','Name','min_length[5]|max_length[256]');
    $description = $form->post('description','Description','max_length[2000]');

    $mathCoursePost = $form->post('math_course');
    if (!$mathCoursePost) {
        $form->error_message("Must provide math course");
        return;
    }

    $mathCourse = '';
    if ($mathCoursePost === "other" || $mathCoursePost === "math_course_2100" || $mathCoursePost === "math_course_2200") {
        $otherField = $form->post('other_math_course');
        if (!$otherField || strlen($otherField) < 4 || strlen($otherField > 50)) {
            $form->error_message("Must provide name of other math course with 4-50 characters");
            return;
        }
        if (Utilities::isInt($otherField)) {
            $otherField = "\"$otherField\"";
        }
        $mathCourse = $otherField;
    } else {
        foreach (MathCourses::getAllCourses() as $mathCourseDatabase) {
            if ($mathCourseDatabase->prefixed_id === $mathCoursePost) {
                $mathCourse = $mathCourseDatabase->id;
            }
        }
    }

    if (!$mathCourse || empty($mathCourse)) {
        $form->error_message("Invalid math course selection");
        return;
    }

    $scheduleJSONString = $form->post('schedule-json');
    if (!$scheduleJSONString || empty($scheduleJSONString)) {
        $form->error_message("You must suggest at least one scheduling option (instance, recurring, or flexible).");
        return;
    }

    if (!$form->errors()) {
        $schedules = TutorRequestDatabase::parseScheduleJSON($scheduleJSONString);
        TutorRequestDatabase::addRequest($_USER, $name, $description, $mathCourse, $schedules);
        Utilities::redirect("https://mathiknow.com/student-portal/");
        return;
    }
}

$form = new Formr('bootstrap');
$form->action = "./";

if ($form->submitted()) {
    verify_form($form);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('New Tutoring Request', ['/assets/css/tutorrequest.css']);
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
            <div class="text-center">
                <?php
                Template::breadcrumb([
                    'Home' => '/',
                    'Student Portal' => '/student-portal',
                    'New Tutoring Request'
                ]);
                ?>
                <div class="card">
                    <div class="card-body">
                        <?php
                        echo $form->messages();
                        echo $form->form_open();
                        ?>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <?= $form->input_text('name', 'Name', $_USER->first_name . '\'s Tutoring Session') ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <?php
                                $options = [];
                                foreach ($userMathCourses as $mathCourse) {
                                    $options[$mathCourse->prefixed_id] = $mathCourse->name;
                                }
                                $options["other"] = "Other";
                                echo $form->input_select('math_course', 'Math Course', '',
                                    '', '', '', '', $options)
                                ?>
                                <p class="form-text text-muted text-left">Select the math course relevant to your request. Click <a href="../change-courses">here</a> to change your courses.</p>
                            </div>
                        </div>
                        <div class="form-row" id="other_math_course" style="display: none;">
                            <div class="form-group col-md-12">
                                <?= $form->input_text('other_math_course', 'Other Math Course')?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <?= $form->input_textarea('description', 'Description')?>
                                <p class="form-text text-muted text-left">Explain what you need help with and any other pertinent information</p>
                            </div>
                        </div>
                        <hr>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="control-label" style="font-size: 30px;">Desired Schedule</label>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="new-schedule-type">Schedule Type</label>
                                <select class="form-control" id="new-schedule-type">
                                    <option value="instance">Specific Date and Time</option>
                                    <option value="recurring">Recurring</option>
                                    <option value="flexible">Flexible / On Demand</option>
                                </select>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div id="select-date-time">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="date-time-start">From</label>
                                            <div class="input-group date" id="date-time-start" data-target-input="nearest">
                                                <input type="text" class="form-control datetimepicker-input" data-target="#date-time-start" />
                                                <div class="input-group-append" data-target="#date-time-start" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                            <p class="form-text text-muted text-left">Provide in your local time (<?=$_USER->getTimezone()->getName()?>). It will be converted to other's timezones.</p>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="date-time-end">To</label>
                                            <div class="input-group date" id="date-time-end" data-target-input="nearest">
                                                <input type="text" class="form-control datetimepicker-input" data-target="#date-time-end" />
                                                <div class="input-group-append" data-target="#date-time-end" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="select-recurring" style="margin-left: 20px; display: none;">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="weekday">On</label>
                                        <select class="form-control" id="weekday">
                                            <?php
                                            foreach (DaysOfWeek::getDays() as $id => $day) {
                                                ?>
                                                <option value="<?=$id?>"><?=$day?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="time-start">From</label>
                                        <div class="input-group date" id="time-start" data-target-input="nearest">
                                            <input type="text" class="form-control datetimepicker-input" data-target="time-start" />
                                            <div class="input-group-append" data-target="#time-start" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                        <p class="form-text text-muted text-left">Provide in your local time (<?=$_USER->getTimezone()->getName()?>). It will be converted to other's timezones.</p>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="time-end">To</label>
                                        <div class="input-group date" id="time-end" data-target-input="nearest">
                                            <input type="text" class="form-control datetimepicker-input" data-target="time-end" />
                                            <div class="input-group-append" data-target="#time-end" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="week-interval">Every</label>
                                        <select class="form-control" id="week-interval">
                                            <?php
                                            for ($i = 1; $i <= 12 * 4; $i++) {
                                                $months = floor($i / 4);
                                                $weeksLeft = $i - $months * 4;
                                                $label = '';
                                                if ($months >= 1) {
                                                    $label = "${months} month";
                                                    if ($months > 1) {
                                                        $label .= 's';
                                                    }
                                                }
                                                if ($weeksLeft >= 1) {
                                                    if ($months >= 1) {
                                                        $label .= ' ';
                                                    }
                                                    $label .= "${weeksLeft} week";
                                                    if ($weeksLeft > 1) {
                                                        $label .= 's';
                                                    }
                                                }
                                                ?>
                                                <option value="<?=$i?>"><?=$label?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="select-flexible" style="display: none;">
                                <div class="form-group col-md-6">
                                    <label for="duration">Estimated Duration per Session</label>
                                    <select class="form-control" id="duration">
                                        <?php
                                        foreach (FlexibleSchedule::$validDurations as $minutes => $label) {
                                            ?>
                                            <option value="<?=$minutes?>"><?=$label?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="alert alert-danger" role="alert" id="schedule-error-box" style="margin-left: 20px; display: none;"></div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <button type="button" class="btn btn-primary btn-block" id="add-schedule" style="margin-left: 20px;">Add</button>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <div class="text-left" id="schedule-dynamic">
                                </div>
                            </div>
                        </div>
                        <div class="form-row" style="display: none;">
                            <div class="form-group col-md-12">
                                <input type="text" id="schedule-json" name="schedule-json" value="<?=isset($_POST['schedule-json']) ? htmlspecialchars($_POST['schedule-json']) : ''?>">
                            </div>
                        </div>
                        <hr>
                        <div class="form-row">
                            <div id="_submit" class="form-group col-md-12" style="padding-left: 20px; padding-right: 20px;">
                                <label class="sr-only" for="submit">Create Request</label>
                                <input type="submit" name="submit" value="Create Request" id="submit" class="btn btn-primary btn-block">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    Template::footer([]);
    ?>
    <script>
    var schedule = [];
    var existingJSON = $("#schedule-json").val();
    if (existingJSON) {
        schedule = JSON.parse(existingJSON);
    }

    var otherTextField = $("#other_math_course");
    $("#math_course").on('change', function () {
        if (this.value === 'other' || this.value === 'math_course_2100' || this.value === 'math_course_2200') {
            otherTextField.show();
        } else {
            otherTextField.hide();
        }
    });

    // Change options
    var selectInstanceDiv = $("#select-date-time");
    var selectRecurringDiv = $("#select-recurring");
    var selectFlexibleDiv = $("#select-flexible");
    var scheduleTypeSelector = $("#new-schedule-type");
    scheduleTypeSelector.on('change', function () {
        selectInstanceDiv.toggle(this.value === 'instance');
        selectRecurringDiv.toggle(this.value === 'recurring');
        selectFlexibleDiv.toggle(this.value === 'flexible');
    });

    var dateTimeStartInput = $('#date-time-start');
    var dateTimeEndInput = $('#date-time-end');

    var timeStartInput = $('#time-start');
    var timeEndInput = $('#time-end');

    var dateTimeStartChanged = false;
    var dateTimeEndChanged = false;
    var startTimeChanged = false;
    var endTimeChanged = false;

    // Date Time Selection
    $(function() {
        dateTimeStartInput.datetimepicker({
            timezone: '<?=$_USER->getTimezone()->getName()?>',
            minDate: new Date(Date.now() - 1000 * 60 * 30 /*30 Minutes ago*/),
            stepping: 15,
            sideBySide: true
        });
        dateTimeEndInput.datetimepicker({
            timezone: '<?=$_USER->getTimezone()->getName()?>',
            stepping: 15,
            sideBySide: true
        });
        dateTimeStartInput.on('change.datetimepicker', function(e) {
            dateTimeEndInput.datetimepicker('minDate', new Date(e.date + 1000 * 60 * 15 - 1000 /*+14m59s*/));
            dateTimeStartChanged = true;
        });
        dateTimeEndInput.on('change.datetimepicker', function(e) {
            //dateTimeStartInput.datetimepicker('maxDate', new Date(e.date - 1000 * 60 * 15 + 1000 /*-15m1s*/));
            dateTimeEndChanged = true;
        });


        timeStartInput.datetimepicker({
            timezone: '<?=$_USER->getTimezone()->getName()?>',
            format: 'LT',
            stepping: 15
        });
        timeEndInput.datetimepicker({
            timezone: '<?=$_USER->getTimezone()->getName()?>',
            format: 'LT',
            stepping: 15
        });
        timeStartInput.on('change.datetimepicker', function (e) {
            timeEndInput.datetimepicker('minDate', new Date(e.date + 1000 * 60 * 15 - 1000 /*+14m59s*/));
            startTimeChanged = true;
        });
        timeEndInput.on('change.datetimepicker', function (e) {
            timeStartInput.datetimepicker('maxDate', new Date(e.date - 1000 * 60 * 15 + 1000 /*-15m1s*/));
            endTimeChanged = true;
        });
    });

    var scheduleErrorBox = $("#schedule-error-box");

    function error(error) {
        scheduleErrorBox.html(error);
        scheduleErrorBox.show();
    }

    function updateScheduleHTML() {
        var json = JSON.stringify(schedule);
        $.ajax({
            type: "post",
            url: "/student-portal/gen-schedule-html.php",
            data: {
                json: json
            },
            success: function (response) {
                $('#schedule-dynamic').html(response);
                $('#schedule-json').val(json);
            }
        });
    }

    // Add schedule button
    $('#add-schedule').on('click', function (e) {
        e.preventDefault();

        scheduleErrorBox.hide();
        scheduleErrorBox.html('');

        var scheduleType = scheduleTypeSelector.val();
        if (scheduleType === 'instance') {
            if (!dateTimeStartChanged && !dateTimeEndChanged) {
                error('Please select a date and time for both start and end.');
                return false;
            }

            var startTimestamp = dateTimeStartInput.datetimepicker('viewDate').unix();
            var endTimestamp = dateTimeEndInput.datetimepicker('viewDate').unix();

            var newScheduleData = {
                type: 'instance',
                index: schedule.length,
                start_timestamp: startTimestamp,
                end_timestamp: endTimestamp
            };

            for (var i = 0; i < schedule.length; i++) {
                if (schedule[i].type === newScheduleData.type &&
                    schedule[i].start_timestamp === newScheduleData.start_timestamp &&
                    schedule[i].end_timestamp === newScheduleData.end_timestamp) {
                    error('You have already added an instance schedule with this date and time.');
                    return false;
                }
            }

            schedule.push(newScheduleData);
        } else if (scheduleType === 'recurring') {
            if (!startTimeChanged || !endTimeChanged) {
                error('Please specify both a start time and an end time');
                return false;
            }

            var dayOfWeek = $('#weekday').val();
            var startMoment = timeStartInput.datetimepicker('viewDate').utc();
            var startMinutes = startMoment.hours() * 60 + startMoment.minutes();
            var endMoment = timeEndInput.datetimepicker('viewDate').utc();
            var endMinutes = endMoment.hours() * 60 + endMoment.minutes();
            var weekInterval = $('#week-interval').val();

            var newScheduleData = {
                type: 'recurring',
                index: schedule.length,
                day_of_week: dayOfWeek,
                start_minute: startMinutes,
                end_minute: endMinutes,
                week_interval: weekInterval
            };

            for (var j = 0; j < schedule.length; j++) {
                if (schedule[j].type === newScheduleData.type &&
                    schedule[j].day_of_week === newScheduleData.day_of_week &&
                    schedule[j].start_minute === newScheduleData.start_minute &&
                    schedule[j].end_minute === newScheduleData.end_minute &&
                    schedule[j].week_interval === newScheduleData.week_interval) {
                    error('You have already added a recurring schedule with these parameters.')
                    return false;
                }
            }

            schedule.push(newScheduleData);
        } else if (scheduleType === 'flexible') {
            var duration = $('#duration').val();
            var newScheduleData = {
                type: 'flexible',
                index: schedule.length,
                duration: duration
            };

            for (var k = 0; k < schedule.length; k++) {
                if (schedule[k].type === newScheduleData.type &&
                    schedule[k].duration === newScheduleData.duration) {
                    error('You have already added a flexible schedule with this duration.')
                    return false;
                }
            }

            schedule.push(newScheduleData);
        }

        updateScheduleHTML();
    });

    function deleteSchedule(index) {
        var realArrayIndex = schedule.findIndex(function(o) {
            return o.index === index;
        });
        if (index !== -1) {
            schedule.splice(realArrayIndex, 1);
        }
        updateScheduleHTML();
    }

    if (schedule.length >= 1) {
        updateScheduleHTML();
    }
    </script>
</body>
</html>