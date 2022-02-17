<?php


namespace MathIKnow;


class GradeLevels {
    public static $gradeLevels = null;

    public static function getLevels() {
        if (self::$gradeLevels == null) {
            self::$gradeLevels = [
                new GradeLevel(6, '6th Grade'),
                new GradeLevel(7, '7th Grade'),
                new GradeLevel(8, '8th Grade'),
                new GradeLevel(9, '9th Grade'),
                new GradeLevel(10, '10th Grade'),
                new GradeLevel(11, '11th Grade'),
                new GradeLevel(12, '12th Grade'),
                new GradeLevel(13, 'College 1st Year'),
                new GradeLevel(14, 'College 2nd Year'),
                new GradeLevel(15, 'College 3rd Year'),
                new GradeLevel(16, 'College 4th Year'),
                new GradeLevel(17, 'College 5th Year'),
                new GradeLevel(18, 'College 6th Year'),
                new GradeLevel(99, 'Graduated College'),
            ];
        }
        return self::$gradeLevels;
    }

    public static function getLevelsFrom($minimum) {
        $levels = [];
        foreach (self::getLevels() as $gradeLevel) {
            if ($gradeLevel->id >= $minimum) {
                $levels[] = $gradeLevel;
            }
        }
        return $levels;
    }

    public static function getLevelsFromAssociative($minimum) : array {
        $levels = self::getLevelsFrom($minimum);
        $array = [];
        foreach ($levels as $level) {
            $array[$level->id] = $level->name;
        }
        return $array;
    }

    public static function doesGradeNumberExist($number) {
        foreach(self::getLevels() as $gradeLevel) {
            if ($gradeLevel->id == $number) {
                return true;
            }
        }
        return false;
    }

    public static function getLevelFromId($id) {
        foreach(self::getLevels() as $gradeLevel) {
            if ($gradeLevel->id == $id) {
                return $gradeLevel;
            }
        }
        return null;
    }
}