<?php


namespace MathIKnow;

use DB;

Database::connect();

class ApplicationDatabase {
    private static $applicationCache = [];

    public static function doesApplicationExist(User $user) {
        $row = DB::queryFirstRow("SELECT * FROM `tutor_apps` WHERE `user`=%i", $user->id);
        return $row != null;
    }

    public static function saveApplication(Application $application) {
        DB::insert('tutor_apps', [
            'user' => $application->getUser()->id,
            'date' => TimeHelper::getUTCTimestamp(),
            'grade_level' => $application->getGrade()->id,
            'courses' => Database::joinList($application->getCoursesIdArray()),
            'course_proof_file' => $application->course_proof_file,
            'availability' => Database::joinList($application->getAvailability()),
            'past_experience' => $application->past_experience
        ]);
        DB::insert('tutor_apps_status',[
            'user' => $application->getUser()->id
        ]);
    }

    public static function getApplication(User $user) {
        $id = $user->id;
        if (isset(self::$applicationCache[$id])) {
            return self::$applicationCache[$id];
        }
        $row = DB::queryFirstRow("SELECT * FROM `tutor_apps` WHERE `user`=%i", $id);
        if ($row == null) {
            return null;
        }
        return self::$applicationCache[$id] = new Application($user,
            GradeLevels::getLevelFromId($row['grade_level']),
            MathCourses::getFromIdArray(Database::expandList($row['courses'])),
            $row['course_proof_file'],
            AvailabilityHelper::getFromIdOrNameArray(Database::expandList($row['availability'])),
            $row['past_experience']
        );
    }

    public static function getStatus(User $user) : ?ApplicationStatus {
        $row = DB::queryFirstRow("SELECT * FROM `tutor_apps_status` WHERE `user`=%i", $user->id);
        if ($row == null) {
            return null;
        }
        return new ApplicationStatus($user, $row['processing'], $row['need_info'], $row['deferred'], $row['accepted'], $row['decision_date']);
    }
}