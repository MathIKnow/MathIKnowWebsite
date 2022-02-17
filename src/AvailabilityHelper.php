<?php


namespace MathIKnow;


class AvailabilityHelper {
    private static $availabilities = null;
    private static $availabilitiesMapped = [];

    /**
     * @return Availability[]
     */
    public static function getAvailabilities() : array{
        if (self::$availabilities == null) {
            self::$availabilities = [
                new Availability(1, "Sunday"),
                new Availability(2, "Sunday Morning"),
                new Availability(3, "Sunday Afternoon"),
                new Availability(4, "Sunday Night"),
                new Availability(5, "Monday"),
                new Availability(6, "Monday Morning"),
                new Availability(7, "Monday Afternoon"),
                new Availability(8, "Monday Night"),
                new Availability(9, "Tuesday"),
                new Availability(10, "Tuesday Morning"),
                new Availability(11, "Tuesday Afternoon"),
                new Availability(12, "Tuesday Night"),
                new Availability(13, "Wednesday"),
                new Availability(14, "Wednesday Morning"),
                new Availability(15, "Wednesday Afternoon"),
                new Availability(16, "Wednesday Night"),
                new Availability(17, "Thursday"),
                new Availability(18, "Thursday Morning"),
                new Availability(19, "Thursday Afternoon"),
                new Availability(20, "Thursday Night"),
                new Availability(21, "Friday"),
                new Availability(22, "Friday Morning"),
                new Availability(23, "Friday Afternoon"),
                new Availability(24, "Friday Night"),
                new Availability(25, "Saturday"),
                new Availability(26, "Saturday Morning"),
                new Availability(27, "Saturday Afternoon"),
                new Availability(28, "Saturday Night"),
            ];
            foreach (self::$availabilities as $availability) {
                self::$availabilitiesMapped[$availability->id] = $availability->name;
            }
        }
        return self::$availabilities;
    }

    public static function getAvailabilitiesAssociative() : array {
        $array = [];
        foreach (self::getAvailabilities() as $availability) {
            $array[$availability->id] = $availability->name;
        }
        return $array;
    }

    public static function getFromId($id) {
        if (!isset(self::$availabilitiesMapped[(int) $id])) {
            return null;
        }
        return self::$availabilitiesMapped[(int) $id];
    }

    public static function getFromIdOrNameArray(array $idArray) {
        self::getAvailabilities(); // Populate if needed
        $availabilities = [];
        foreach ($idArray as $id) {
            $value = self::getFromId($id);
            if ($value) {
                foreach (self::getAvailabilities() as $availability) {
                    if ($availability->name == $id) {
                        $value = $availability;
                    }
                }
            }
            if ($value) {
                $availabilities[] = $value;
            }
        }
        return $availabilities;
    }
}