<?php


namespace MathIKnow;

use DB;

Database::connect();

class TutorRequestDatabase {
    public static function addRequest(User $user, string $name, string $description, string $mathCourse,
                                      array $schedules) {
        DB::insert('tutoring_requests', [
            'student_id' => $user->id,
            'date' => TimeHelper::getUTCTimestamp(),
            'name' => $name,
            'description' => $description,
            'math_course' => $mathCourse
        ]);

        if ($schedules) {
            $requestId = DB::insertId();
            self::updateSchedule($requestId, $schedules);
        }
    }

    /**
     * @param $requestId
     * @param Schedule[] $schedules
     */
    public static function updateSchedule($requestId, array $schedules) {
        $array = [];
        foreach ($schedules as $schedule) {
            $array[] = $schedule->toArray();
        }

        DB::update('tutoring_requests', ['schedule_json' => json_encode($array)], "id=%i", $requestId);
    }

    public static function updateRequest(int $requestId, string $name, string $description, array $mathCourse,
        array $schedules) {
        DB::insertUpdate('tutoring_requests', [
            'id' => $requestId,
            'date' => TimeHelper::getUTCTimestamp(),
            'name' => $name,
            'description' => $description,
            'math_course' => $mathCourse
        ]);
        if ($schedules) {
            self::updateSchedule($requestId, $schedules);
        }
    }

    private static function getRequestFromRow(array $row) : TutorRequest {
        $user = UserDatabase::getUserFromId($row['student_id']);
        $schedulesArray = self::parseScheduleJSON($row['schedule_json']);
        $requestId = $row['id'];

        $tutorsReachedOut = [];
        $tutorsClaimed = [];
        $rows = DB::query('SELECT * FROM `tutoring_requests_status` WHERE `request_id`=%i', $requestId);
        if ($rows && sizeof($rows) >= 1) {
            foreach ($rows as $statusRow) {
                $tutorId = $statusRow['tutor_id'];
                if ($statusRow['reached_out'] == true) {
                    $tutorsReachedOut[] = UserDatabase::getUserFromId($tutorId);
                }
                if ($statusRow['claimed'] == true) {
                    $tutorsClaimed[] = UserDatabase::getUserFromId($tutorId);
                }
            }
        }

        return new TutorRequest($requestId, $user, $row['date'], $row['name'], $row['description'],
            $schedulesArray, $tutorsReachedOut, $tutorsClaimed, $row['math_course'], $row['archived']);
    }

    public static function getRequestById(int $requestId) : ?TutorRequest {
        $row = DB::queryOneRow('SELECT * FROM `tutoring_requests` WHERE `id`=%i', $requestId);
        if ($row == null) {
            return null;
        }
        return self::getRequestFromRow($row);
    }

    /**
     * @param User $user
     * @return TutorRequest[]
     */
    public static function getRequests(User $user) : array {
        $userId = $user->id;
        $rows = DB::query('SELECT * FROM `tutoring_requests` WHERE `student_id`=%i', $userId);
        $requests = [];
        foreach ($rows as $row) {
            $requests[] = self::getRequestFromRow($row); // TODO : Efficiency via JOIN
        }
        return $requests;
    }

    /**
     * @return TutorRequest[]
     */
    public static function getAllRequests() : array {
        $rows = DB::query('SELECT * FROM `tutoring_requests`');
        $requests = [];
        foreach ($rows as $row) {
            $requests[] = self::getRequestFromRow($row); // TODO : Efficiency via JOIN
        }
        return $requests;
    }

    public static function deleteRequest(int $requestId) {
        DB::delete('tutoring_requests', 'id=%i', $requestId);
    }

    public static function archiveRequest(int $requestId) {
        DB::update('tutoring_requests', ['archived' => true], '`id`=%i', $requestId);
    }

    public static function unarchiveRequest($requestId) {
        DB::update('tutoring_requests', ['archived' => false], '`id`=%i', $requestId);
    }

    private static function addLocalIndex($scheduleJSON, Schedule &$schedule) {
        if (isset($scheduleJSON['index'])) {
            $schedule->setLocalIndex($scheduleJSON['index']);
        }
    }

    private static function compareInstance($scheduleA, $scheduleB) {
        $scheduleAstart = $scheduleA->start_timestamp;
        $scheduleBstart = $scheduleB->start_timestamp;
        return $scheduleAstart - $scheduleBstart;
    }

    private static function compareRecurring($scheduleA, $scheduleB) {
        $a_weekInterval = $scheduleA->weekInterval;
        $b_weekInterval = $scheduleB->weekInterval;
        if ($a_weekInterval != $b_weekInterval) {
            return $a_weekInterval - $b_weekInterval;
        }

        $a_weekDay = $scheduleA->weekday;
        $b_weekDay = $scheduleB->weekday;
        if ($a_weekDay != $b_weekDay) {
            return $a_weekDay - $b_weekDay;
        }

        $a_startMinute = $scheduleA->startMinute;
        $b_startMinute = $scheduleB->startMinute;
        if ($a_startMinute != $b_startMinute) {
            return $a_startMinute - $b_startMinute;
        }

        $a_endMinute = $scheduleA->endMinute;
        $b_endMinute = $scheduleB->endMinute;
        return $a_endMinute - $b_endMinute;
    }

    private static function compareFlexible($scheduleA, $scheduleB) {
        $aDuration = $scheduleA->duration;
        $bDuration = $scheduleB->duration;

        if ($aDuration == "On Demand Messaging") {
            return -1;
        }
        if ($bDuration == "On Demand Messaging") {
            return 1;
        }

        return $aDuration - $bDuration;
    }

    public static function parseScheduleJSON($jsonString) : array {
        $jsonArray = json_decode($jsonString, true);

        $instanceSchedules = [];
        $recurringSchedules = [];
        $flexibleSchedules = [];

        foreach ($jsonArray as $scheduleJSON) {
            $type = $scheduleJSON['type'];
            $schedule = null;
            if ($type === 'instance') {
                $schedule = InstanceSchedule::fromArray($scheduleJSON);
                self::addLocalIndex($scheduleJSON, $schedule);
                $instanceSchedules[] = $schedule;
            } else if ($type === 'recurring') {
                $schedule = RecurringSchedule::fromArray($scheduleJSON);
                self::addLocalIndex($scheduleJSON, $schedule);
                $recurringSchedules[] = $schedule;
            } else if ($type === 'flexible') {
                $schedule = FlexibleSchedule::fromArray($scheduleJSON);
                self::addLocalIndex($scheduleJSON, $schedule);
                $flexibleSchedules[] = $schedule;
            }
        }

        uasort($instanceSchedules, array('MathIKnow\TutorRequestDatabase', 'compareInstance'));
        uasort($recurringSchedules, array('MathIKnow\TutorRequestDatabase', 'compareRecurring'));
        uasort($flexibleSchedules, array('MathIKnow\TutorRequestDatabase', 'compareFlexible'));

        return array_merge($instanceSchedules, $recurringSchedules, $flexibleSchedules);
    }

    public static function updateReachedOut(int $requestId, User $tutor, bool $reachedOut) {
        DB::insertUpdate('tutoring_requests_status', [
            'request_id' => $requestId,
            'tutor_id' => $tutor->id,
            'reached_out' => $reachedOut
        ]);
    }

    public static function updateClaimed(int $requestId, User $tutor, bool $claimed) {
        DB::insertUpdate('tutoring_requests_status', [
            'request_id' => $requestId,
            'tutor_id' => $tutor->id,
            'claimed' => $claimed
        ]);
    }
}