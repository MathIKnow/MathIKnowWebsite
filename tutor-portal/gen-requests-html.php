<?php

include __DIR__ . '/../vendor/autoload.php';

use MathIKnow\InstanceSchedule;
use MathIKnow\MathCourses;
use MathIKnow\RecurringSchedule;
use MathIKnow\RequestWeighAlgorithm;
use MathIKnow\TutorRequest;
use MathIKnow\TutorRequestDatabase;
use MathIKnow\UserDatabase;
use MathIKnow\Utilities;

$_USER = UserDatabase::getUserFromBrowser();

if (!$_USER || !$_USER->isInGroup('tutor_approved')) {
    http_response_code(403);
    return;
}

/*ob_start();
var_dump($_POST);
$post = ob_get_clean();
echo "<p>POST: $post</p>";*/

$page = $_POST['page'] ?? 1;

$sortBy = $_POST['sort_by'] ?? 'default';

$limitTo = null;
if (isset($_POST['limit_to']) && Utilities::isInt($_POST['limit_to'])) {
    $limitTo = (int) $_POST['limit_to'];
}

$filterContains = null;
if (isset($_POST['filter_contains']) && !empty($_POST['filter_contains'])) {
    $filterContains = $_POST['filter_contains'];
}

$filterAfter = null;
if (isset($_POST['filter_after'])) {
    $filterAfter = $_POST['filter_after'];
}

$filterBefore = null;
if (isset($_POST['filter_before'])) {
    $filterBefore = $_POST['filter_before'];
}

$showArchived = true;
if (isset($_POST['show_archived'])) {
    $showArchived = filter_var($_POST['show_archived'], FILTER_VALIDATE_BOOLEAN);
}

$courseTypesStrings = null;
if (isset($_POST['math_courses'])) {
    $courseTypesStrings = explode(',', $_POST['math_courses']);
}

$courses = null;
$other = false;
if ($courseTypesStrings) {
    foreach ($courseTypesStrings as $courseString) {
        if (strtolower($courseString) === "other") {
            $other = true;
            continue;
        }
        if ($courses == null) {
            $courses = [];
        }

        $course = MathCourses::getCourseFromId($courseString);
        if ($course) {
            $courses[] = $course;
        }
    }
}

/*$dump = Utilities::getVarDump($courses);
echo "<p>Courses: $dump</p>";
$dump = Utilities::getVarDump($other);
echo "<br><p>Other: $dump</p>";*/

$requests = TutorRequestDatabase::getAllRequests();

// FILTERING
if (!$showArchived) {
    function filterNoArchived(TutorRequest $request) {
        return !$request->archived;
    }
    $requests = array_filter($requests, 'filterNoArchived');
}

if ($filterContains) {
    function filterContains(TutorRequest $request) {
        global $filterContains, $_USER;

        $fullText = '';
        $fullText .= $request->name;
        $fullText .= "Description: ". $request->description;
        $fullText .= $request->user->username;
        $fullText .= $request->user->first_name;
        $fullText .= $request->user->last_name;
        $fullText .= $request->user->username . " (" . $request->user->email . ")";
        $fullText .= $request->user->id;
        $fullText .= $request->mathCourse;
        $mathCourse = $request->getKnownMathCourse();
        if ($mathCourse) {
            $fullText .= $mathCourse->name;
        }
        $preferences = UserDatabase::getPreferences($request->user);
        if ($preferences) {
            $fullText .= "Preferred Contact Method: " . $preferences->contactMethod;
        }
        foreach (array_merge($request->tutorsReachedOut, $request->tutorsClaimed) as $tutor) {
            $fullText .= $tutor->username;
            $fullText .= $tutor->first_name;
            $fullText .= $tutor->last_name;
        }
        foreach ($request->schedules as $schedule) {
            $fullText .= "Course: " . $schedule->getUserTimeDescription($_USER);
        }
        return str_contains(strtolower($fullText), strtolower($filterContains));
    }
    $requests = array_filter($requests, 'filterContains');
}

if ($filterAfter) {
    function filterAfter(TutorRequest $request) {
        global $filterAfter;
        return $request->timestamp >= $filterAfter;
    }
    $requests = array_filter($requests, 'filterAfter');
}

if ($filterBefore) {
    function filterBefore(TutorRequest $request) {
        global $filterBefore;
        return $request->timestamp <= $filterBefore;
    }
    $requests = array_filter($requests, 'filterBefore');
}

function filterCourses(TutorRequest $request) {
    global $courses, $other;

    $requestCourse = $request->getKnownMathCourse();
    if ($requestCourse != null) {
        foreach ($courses as $tutorCourse) {
            if ($requestCourse->id === $tutorCourse->id) {
                // Matches
                return true;
            }
        }
    } else {
        // Is null, keep only if they selected other
        return $other;
    }
    // Does not match
    return false;
}
$requests = array_filter($requests, 'filterCourses');

// SORTING

function sortDefault(TutorRequest $requestA, TutorRequest $requestB) {
    global $_USER;

    $algA = new RequestWeighAlgorithm($requestA, $_USER);
    $weighA = $algA->calculateWeight();
    $algB = new RequestWeighAlgorithm($requestB, $_USER);
    $weighB = $algB->calculateWeight();

    if ($weighA != $weighB) {
        return ($weighA > $weighB) ? -1 : 1;
    } else {
        $timeA = $requestA->timestamp;
        $timeB = $requestB->timestamp;
        // if A is less, return -1
        return $timeA - $timeB;
    }
}

function sortOldest(TutorRequest $requestA, TutorRequest $requestB) {
    // if A is less, return -1
    return $requestA->timestamp - $requestB->timestamp;
}

function sortNewest(TutorRequest $requestA, TutorRequest $requestB) {
    return -1 * sortOldest($requestA, $requestB);
}

function calculateScheduleWeight(TutorRequest $request) {
    $weight = 0;
    foreach ($request->schedules as $schedule) {
        if ($schedule instanceof RecurringSchedule) {
            $weight += 3;
        } else if ($schedule instanceof InstanceSchedule) {
            $weight += 2;
        } else if ($schedule instanceof \MathIKnow\FlexibleSchedule) {
            $weight += 1;
        }
    }
    return $weight;
}

function sortSessionType(TutorRequest $requestA, TutorRequest $requestB) {
    $weightA = calculateScheduleWeight($requestA);
    $weightB = calculateScheduleWeight($requestB);
    // if A is more, return -1
    return $weightB - $weightA;
}

function calculateSessionLength(TutorRequest  $request) {
    $length = 0;
    foreach ($request->schedules as $schedule) {
        $length += $schedule->getDurationMinutes();
    }
    return $length;
}

function sortSessionLengthAscending(TutorRequest $requestA, TutorRequest $requestB) {
    $lengthA = calculateSessionLength($requestA);
    $lengthB = calculateSessionLength($requestB);
    // if A is less, return -1
    return $lengthA - $lengthB;
}

function sortSessionLengthDescending(TutorRequest $requestA, TutorRequest $requestB) {
    return -1 * sortSessionLengthAscending($requestA, $requestB);
}

function sortNameAscending(TutorRequest $requestA, TutorRequest $requestB) {
    return strcmp($requestA->name, $requestB->name);
}

function sortNameDescending(TutorRequest $requestA, TutorRequest $requestB) {
    return -1 * sortNameAscending($requestA, $requestB);
}

function getCourseName(TutorRequest $request) {
    if ($request->getKnownMathCourse()) {
        return $request->getKnownMathCourse()->name;
    } else {
        return $request->mathCourse;
    }
}

function sortCourseNameAscending(TutorRequest $requestA, TutorRequest $requestB) {
    return strcmp(getCourseName($requestA), getCourseName($requestB));
}

function sortCourseNameDescending(TutorRequest $requestA, TutorRequest $requestB) {
    return -1 * sortCourseNameAscending($requestA, $requestB);
}

function calculateDifficulty(TutorRequest $request) {
    $mathCourse = $request->getKnownMathCourse();
    if ($mathCourse) {
        return $mathCourse->id;
    }
    return 0;
}

function sortDifficultyAscending(TutorRequest $requestA, TutorRequest $requestB) {
    // if A is less, return -1
    return calculateDifficulty($requestA) - calculateDifficulty($requestB);
}

function sortDifficultyDescending(TutorRequest $requestA, TutorRequest $requestB) {
    return -1 * sortDifficultyAscending($requestA, $requestB);
}

$sortFunction = 'sortDefault';
switch ($sortBy) {
    case 'oldest':
        $sortFunction = 'sortOldest';
        break;
    case 'newest':
        $sortFunction = 'sortNewest';
        break;
    case 'session_type':
        $sortFunction = 'sortSessionType';
        break;
    case 'session_length_ascending':
        $sortFunction = 'sortSessionLengthAscending';
        break;
    case 'session_length_descending':
        $sortFunction = 'sortSessionLengthDescending';
        break;
    case 'name_ascending':
        $sortFunction = 'sortNameAscending';
        break;
    case 'name_descending':
        $sortFunction = 'sortNameDescending';
        break;
    case 'course_name_ascending':
        $sortFunction = 'sortCourseNameAscending';
        break;
    case 'course_name_descending':
        $sortFunction = 'sortCourseNameDescending';
        break;
    case 'course_difficulty_ascending':
        $sortFunction = 'sortDifficultyAscending';
        break;
    case 'course_difficulty_descending':
        $sortFunction = 'sortDifficultyDescending';
        break;
    case 'default':
    default:
        $sortFunction = 'sortDefault';
        break;
}

uasort($requests, $sortFunction);

$totalPages = 1;
// LIMITING
if ($limitTo) {
    $chunked = array_chunk($requests, $limitTo);
    $totalPages = sizeof($chunked);
    if ($page < 1) {
        $page = 1;
    }
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $requests = $chunked[$page - 1];
}

?>
<script>
totalPages = <?=$totalPages?>;
$("#out-of-page").html('out of <?=$totalPages?>');
$("#page").val('<?=$page?>');
<?php


if ($page == 1) {
?>
previousButton.addClass('disabled');
<?php
} else {
?>
previousButton.removeClass('disabled');
<?php
}


if ($page >= $totalPages) {
?>
nextButton.addClass('disabled');
<?php
} else {
?>
nextButton.removeClass('disabled');
<?php
}

if ($totalPages <= 1) {
?>
navigationButtons.hide();
<?php
} else {
?>
navigationButtons.show();
<?php
}
?>
</script>
<?php

foreach ($requests as $request) {
    $request->echoTutorPortalHTML($_USER);
}