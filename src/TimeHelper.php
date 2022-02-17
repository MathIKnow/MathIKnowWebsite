<?php

namespace MathIKnow;

use DateTime;
use DateTimeZone;

class TimeHelper {
    private static $format = 'Y-m-d g:i A';

    public static function getUTCTimestamp() {
        return self::getTimestamp('UTC');
    }

    public static function getTimestamp($timezone) {
        $now = new DateTime('now', new DateTimeZone($timezone));
        return $now->getTimestamp();
    }

    public static function getDateTime($utcTimestamp) {
        return new DateTime("@" . $utcTimestamp, new DateTimeZone('UTC'));
    }

    public static function getDateTimeForUser($utcTimestamp, User $user) {
        $date = self::getDateTime($utcTimestamp);
        $date->setTimezone($user->getTimezone());
        return $date;
    }

    public static function formatForUser($utcTimestamp, User $user) {
        return self::formatTime(self::getDateTimeForUser($utcTimestamp, $user));
    }

    public static function formatTime(DateTime $date) {
        return $date->format(self::$format);
    }

    public static function getTimezoneDropdown() {
        static $timezones = null;
        if ($timezones == null) {
            $timezones = [];
            $offsets = [];
            $now = new DateTime('now', new DateTimeZone('UTC'));

            foreach (DateTimeZone::listIdentifiers() as $timezone) {
                $now->setTimezone(new DateTimeZone($timezone));
                $offsets[] = $offset = $now->getOffset();
                $timezones[$timezone] = self::formatTimezoneName($timezone) .
                    ' (' . self::formatGMTOffset($offset) . ') ';
            }
            asort($timezones);
        }
        return $timezones;
    }

    public static function getTimezoneIdentifiers() {
        return array_keys(self::getTimezoneDropdown());
    }

    public static function getTimeZoneFromIdentifier($identifier) : DateTimeZone {
        return new DateTimeZone($identifier);
    }

    public static function getTimezoneOffsetSecondsFromTimezone(DateTimeZone  $timezone) : int {
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $now->setTimezone($timezone);
        return $now->getOffset();
    }

    public static function getTimezoneOffsetSecondsFromIdentifier(string $identifier) {
        return self::getTimezoneOffsetSecondsFromTimezone(self::getTimeZoneFromIdentifier($identifier));
    }

    static function formatGMTOffset($offset) {
        $hours = intval($offset / 3600);
        $minutes = abs(intval($offset % 3600 / 60));
        return 'GMT' . ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');
    }

    public static function formatTimezoneName($name) {
        $name = str_replace('/', ', ', $name);
        $name = str_replace('_', ' ', $name);
        $name = str_replace('St ', 'St. ', $name);
        return $name;
    }
}