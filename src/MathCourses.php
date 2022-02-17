<?php
namespace MathIKnow;

use DB;

Database::connect();

class MathCourses {
    private static $course_prefix = "math_course_";
    private static $courses = null;
    private static $coursesMapped = [];

    /**
     * @return ?MathCourse[]
     */
    public static function getAllCourses() {
        if (self::$courses == null) {
            $rows = DB::query('SELECT * FROM `math_courses`');
            self::$courses = [];
            foreach ($rows as $row) {
                $id = $row['id'];
                $prefixed_id = MathCourses::$course_prefix . $id;
                $course = new MathCourse($id, $prefixed_id, $row['name']);
                self::$courses[] = $course;
                self::$coursesMapped[$id] = $course;
            }
        }
        return self::$courses;
    }

    public static function getCourseFromId($id) : ?MathCourse {
        self::getAllCourses(); // Populate if needed
        return self::$coursesMapped[(int) $id];
    }

    public static function getFromIdArray(array $idArray) : array{
        self::getAllCourses(); // Populate if needed
        $courses = [];
        foreach($idArray as $id) {
            $courses[] = self::getCourseFromId($id);
        }
        return $courses;
    }

    /**
     * @param MathCourse[] $mathCourses
     * @return int[]
     */
    public static function toIdArray(array $mathCourses) : array {
        $idArray = [];
        foreach ($mathCourses as $mathCourse) {
            $idArray[] = $mathCourse->id;
        }
        return $idArray;
    }
}