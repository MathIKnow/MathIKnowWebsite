<?php


namespace MathIKnow;


class DaysOfWeek {
    private static $days = [];
    private static $normalWeekdays = [1 => "Sunday", 2 => "Monday", 3 => "Tuesday", 4 => "Wednesday", 5 => "Thursday",
        6 => "Friday", 7 => "Saturday"];

    public static function getDays() {
        if (sizeof(self::$days) == 0) {
            self::$days = ["Any", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        }
        return array_values(self::$days);
    }

    public static function getFromId($id) {
        return self::$days[$id];
    }

    public static function getFromIdArray(array $idArray) : array {
        self::getDays(); // Populate if needed
        $days = [];
        foreach ($idArray as $id) {
            $days[] = self::getFromId($id);
        }
        return $days;
    }

    public static function getById($id) {
        foreach (self::getDays() as $dayId => $day) {
            if ($dayId == $id) {
                return $day;
            }
        }
        return null;
    }

    public static function toId($dayName) {
        foreach (self::getDays() as $dayId => $day) {
            if ($day == $dayName) {
                return $dayId;
            }
        }
        return null;
    }

    public static function getIdOfDayBeforeId($id) {
        if ($id == 0) {
            return 0;
        }
        if ($id == 1) {
            return 7;
        }
        return $id - 1;
    }

    public static function getIdOfDayAfterId($id) {
        if ($id == 0) {
            return 0;
        }
        if ($id == 7) {
            return 1;
        }
        return $id + 1;
    }
}