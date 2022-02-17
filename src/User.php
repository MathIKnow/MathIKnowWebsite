<?php

namespace MathIKnow;

use DateTimeZone;

class User {
    public $id, $email, $username, $verified, $first_name, $last_name, $signup_ip,
        $signup_date, $geo_region, $geo_country, $timezone, $timezone_offset, $groupsMapping;
    public $mathCourses;

    public function __construct($id, $email, $username, $verified, $first_name, $last_name, $signup_ip, $signup_date, $geo_region, $geo_country, $timezone, $timezone_offset, $groupsMapping) {
        $this->id = $id;
        $this->email = $email;
        $this->username = $username;
        $this->verified = $verified;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->signup_ip = $signup_ip;
        $this->signup_date = $signup_date;
        $this->geo_region = $geo_region;
        $this->geo_country = $geo_country;
        $this->timezone = $timezone;
        $this->timezone_offset = $timezone_offset;
        $this->groupsMapping = $groupsMapping;
    }

    public function isInGroup($group) {
        return array_key_exists($group, $this->groupsMapping) && $this->groupsMapping[$group];
    }

    public function getGroupNames() {
        $groupNames = [];
        foreach ($this->groupsMapping as $groupName => $isInGroup) {
            if ($isInGroup) {
                $groupNames[] = $groupName;
            }
        }
        return $groupNames;
    }

    public function getTimezone() : DateTimeZone
    {
        return TimeHelper::getTimeZoneFromIdentifier($this->timezone);
    }

    public static function createFromDatabase($data, $groupsMapping) {
        return new User($data['id'], $data['email'], $data['username'], $data['verified'], $data['first_name'],
            $data['last_name'], $data['signup_ip'], $data['signup_date'], $data['geo_region'], $data['geo_country'],
            $data['timezone'], $data['timezone_offset'], $groupsMapping);
    }
}