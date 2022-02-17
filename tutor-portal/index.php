<?php
include __DIR__ . '/../vendor/autoload.php';

use MathIKnow\MathCourses;
use MathIKnow\Template;
use MathIKnow\UserDatabase;
use MathIKnow\Utilities;

$_USER = UserDatabase::getUserFromBrowser();

if (!isset($_USER)) {
    Utilities::redirect("https://mathiknow.com/login");
}
if (!$_USER->isInGroup('tutor')) {
    Utilities::redirect("https://mathiknow.com/");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    Template::head('MathIKnow - Tutor Portal', ['/assets/css/tutor-portal.css']);
    ?>
</head>

<body>
<div class="d-flex flex-column sticky-footer-wrapper">
    <nav>
        <?php
        Template::navbar($_USER, false, 'tutor-portal');
        ?>
    </nav>
    <main class="flex-fill">
        <div class="container main-container">
            <?php
            Template::breadcrumb([
                'Home' => '/',
                'Tutor Portal'
            ]);
            ?>
            <!--<div class="text-center">
                <h1 class="display-4 d-none d-lg-block">Tutor Portal</h1>
                <h1 class="d-lg-none d-xl-none">Tutor Portal</h1>
                <div class="card">
                    <div class="card-body">
                        <h3>All Tutoring Requests:</h3>
                        <div id="tutoring-requests" class="text-left">
                            <?php
                            /*$allRequests = TutorRequestDatabase::getAllRequests();
                            foreach ($allRequests as $request) {
                                $request->echoTutorPortalHTML($_USER);
                            }*/
                            ?>
                        </div>
                    </div>
                </div>
            </div>-->
            <div class="text-center">
                <div class="card">
                    <div class="card-body">
                        <div class="text-left">
                            <a href="/settings/link-discord/" class="btn btn-success btn-lg active portal-button" role="button">Link Discord</a>
                        </div>
                        <br>
                        <br>
                        <h3>Requests:</h3>
                        <div class="container">
                            <div class="row">
                                <div class="col-md-3 text-left">
                                    <div class="card filter">
                                        <div class="card-body">
                                            <p class="card-title filter-title">Page:</p>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="page" id="page" value="1"/>
                                                <div class="input-group-append">
                                                    <div class="input-group-text" id="out-of-page">out of 1</div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="btn-group" role="group" aria-label="navigation-buttons" id="navigation-buttons">
                                                <button type="button" class="btn btn-secondary" id="previous-button">Previous</button>
                                                <button type="button" class="btn btn-secondary" id="next-button">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card filter">
                                        <div class="card-body">
                                            <p class="card-title filter-title">Sort By:</p>
                                            <select class="form-control" id="sort-by">
                                                <option value="default" selected>Default</option>
                                                <option value="oldest">Oldest</option>
                                                <option value="newest">Newest</option>
                                                <option value="session_type">Schedule Type</option>
                                                <option value="session_length_ascending">Duration (Ascending)</option>
                                                <option value="session_length_descending">Duration (Descending)</option>
                                                <option value="name_ascending">Name (A -> Z)</option>
                                                <option value="name_descending">Name (Z -> A)</option>
                                                <option value="course_name_ascending">Course Name (A -> Z)</option>
                                                <option value="course_name_descending">Course Name (Z -> A)</option>
                                                <option value="course_difficulty_descending">Math Course (Hardest)</option>
                                                <option value="course_difficulty_ascending">Math Course (Easiest)</option>
                                            </select>
                                            <p class="card-title filter-title">Limit To:</p>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="limit-to" id="limit-to" value="100"/>
                                                <div class="input-group-append">
                                                    <div class="input-group-text">per page</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card filter">
                                        <div class="card-body">
                                            <p class="card-title filter-title">Filter By:</p>
                                            <hr>
                                            <label for="search-for">Contains</label>
                                            <input type="text" class="form-control" name="search-for" id="search-for" value=""/>
                                            <hr>
                                            <label for="date-time-after">After</label>
                                            <div class="input-group date" id="date-time-after" data-target-input="nearest">
                                                <input type="text" class="form-control datetimepicker-input" data-target="#date-time-after" />
                                                <div class="input-group-append" data-target="#date-time-after" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                            <label for="date-time-before">Before</label>
                                            <div class="input-group date" id="date-time-before" data-target-input="nearest">
                                                <input type="text" class="form-control datetimepicker-input" data-target="#date-time-before" />
                                                <div class="input-group-append" data-target="#date-time-before" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div style="margin-top: 20px; margin-bottom: -10px;">
                                                <input type="checkbox" name="show-archived" value="show-archived" id="show-archived" checked>
                                                <label for="show-archived">Show Archived</label>
                                            </div>
                                            <hr>
                                            <fieldset>
                                                <p>Courses</p>
                                                <?php
                                                foreach (MathCourses::getAllCourses() as $course) {
                                                    if ($course->id == 2100 || $course->id == 2200) {
                                                        continue;
                                                    }
                                                    ?>
                                                <div>
                                                    <input type="checkbox" class="math-course-checkbox" name="<?=$course->id?>" value="<?=$course->id?>" id="<?=$course->id?>" checked>
                                                    <label for="<?=$course->id?>" class="course-name"><?=$course->name?></label>
                                                </div>
                                                    <?php
                                                }
                                                ?>
                                                <div>
                                                    <input type="checkbox" class="math-course-checkbox" name="other" value="other" id="Other" checked>
                                                    <label for="other" class="course-name">Other</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9 text-left">
                                    <div class="spinner-border text-primary text-center" role="status" id="spinner">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div id="dynamic-requests"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    Template::footer();
    ?>
    <script>
    var spinner = $("#spinner");

    var totalPages = 1;
    var pageInput = $("#page");
    var previousButton = $("#previous-button");
    var nextButton = $("#next-button");
    var navigationButtons = $("#navigation-buttons");

    // TODO : PREFILL FILTERS FROM GET VAR!
    var afterChanged = false;
    var beforeChanged = false;

    var dateAfterPicker = $("#date-time-after");
    var dateBeforePicker = $("#date-time-before");

    dateAfterPicker.datetimepicker({
        timezone: '<?=$_USER->getTimezone()->getName()?>',
        stepping: 15
    });
    dateBeforePicker.datetimepicker({
        timezone: '<?=$_USER->getTimezone()->getName()?>',
        stepping: 15
    });

    var dynamicRequestsDiv = $("#dynamic-requests");

    var sortBySelect = $("#sort-by");
    var limitToInput = $("#limit-to");
    var containsInput = $("#search-for");
    var showArchivedCheckbox = $("#show-archived");
    var courseCheckboxes = $('input:checkbox.math-course-checkbox');

    function updateRequests() {
        //spinner.show();
        var data = {
            page: pageInput.val(),
            sort_by: sortBySelect.val(),
            limit_to: limitToInput.val(),
            filter_contains: containsInput.val(),
            show_archived: showArchivedCheckbox.prop('checked')
        };

        if (afterChanged) {
            data.filter_after = dateAfterPicker.datetimepicker('viewDate').unix();
        }
        if (beforeChanged) {
            data.filter_before = dateBeforePicker.datetimepicker('viewDate').unix();
        }

        var mathCoursesArray = [];
        courseCheckboxes.each(function () {
            if (this.checked) {
                mathCoursesArray.push($(this).val());
            }
        });
        data.math_courses = mathCoursesArray.join(",");

        $.ajax({
            type: "post",
            url: "/tutor-portal/gen-requests-html.php",
            data: data,
            success: function (response) {
                dynamicRequestsDiv.html(response);
                spinner.hide();
            }
        })
    }

    updateRequests();

    pageInput.on('change', function() {
        updateRequests();
    });
    sortBySelect.on('change', function() {
        updateRequests();
    });
    limitToInput.on('change', function() {
        updateRequests();
    });
    containsInput.on('change', function() {
        updateRequests();
    });
    dateAfterPicker.on('change.datetimepicker', function(e) {
        afterChanged = true;
        updateRequests();
    });
    dateBeforePicker.on('change.datetimepicker', function (e) {
        beforeChanged = true;
        updateRequests();
    });
    showArchivedCheckbox.on('change', function() {
       updateRequests();
    });
    courseCheckboxes.change(function() {
       updateRequests();
    });

    previousButton.on('click', function() {
        var currentPage = parseInt(pageInput.val());
        if (currentPage) {
            if (currentPage <= 1) {
                return;
            }
            pageInput.val(currentPage - 1);
            updateRequests();
        }
    });
    nextButton.on('click', function () {
        var currentPage = parseInt(pageInput.val());
        if (currentPage) {
            if (currentPage >= totalPages) {
                return;
            }
            pageInput.val(currentPage + 1);
            updateRequests();
        }
    });
    // Action Checkboxes
    $(document).on('change', '.action-checkbox', function() {
        var name = $(this).val();
        var checked = $(this).is(':checked');
        if (name.startsWith('contacted-')) {
            var id = name.replace("contacted-", "");
            $.ajax({
                type: "post",
                url: "/tutor-portal/action-checkbox.php",
                data: {
                    'request_id': id,
                    'action': 'reached_out',
                    'checked': checked
                },
                success: function () {
                    setTimeout(function() {
                        updateRequests();
                    }, 250);
                }
            });
        } else if (name.startsWith('claimed-')) {
            var id = name.replace("claimed-", "");
            $.ajax({
                type: "post",
                url: "/tutor-portal/action-checkbox.php",
                data: {
                    'request_id': id,
                    'action': 'claimed',
                    'checked': checked
                },
                success: function () {
                    setTimeout(function() {
                        updateRequests();
                    }, 250);
                }
            });
        }
    });
    </script>
</div>
</body>
</html>