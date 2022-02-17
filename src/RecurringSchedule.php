<?php


namespace MathIKnow;


class RecurringSchedule implements Schedule {
    public $weekday, $weekInterval, $startMinute, $endMinute, $localIndex = -1;

    public function __construct(int $weekday, int $weekInterval, int $startMinute, int $endMinute) {
        $this->weekday = $weekday;
        $this->weekInterval = $weekInterval;
        $this->startMinute = $startMinute;
        $this->endMinute = $endMinute;
    }

    public function isRecurring() {
        return true;
    }

    public function getDurationMinutes() {
        return $this->endMinute - $this->startMinute;
    }

    /**
     * @return int
     */
    public function getWeekday(): int
    {
        return $this->weekday;
    }

    /**
     * @return int
     */
    public function getWeekInterval(): int
    {
        return $this->weekInterval;
    }

    /**
     * @return int
     */
    public function getStartMinute(): int
    {
        return $this->startMinute;
    }

    /**
     * @return int
     */
    public function getEndMinute(): int
    {
        return $this->endMinute;
    }

    public function toArray() {
        return [
            'type' => 'recurring',
            'day_of_week' => $this->weekday,
            'start_minute' => $this->startMinute,
            'end_minute' => $this->endMinute,
            'week_interval' => $this->weekInterval
        ];
    }

    public static function fromArray($array) {
        return new RecurringSchedule($array['day_of_week'], $array['week_interval'], $array['start_minute'],
        $array['end_minute']);
    }

    public function minutesToReadable($minutes) {
        $hours = floor($minutes / 60);
        $minutes = $minutes - $hours * 60;

        $am = true;

        if ($hours > 12) {
            $am = false;
            $hours -= 12;
        }

        $ampm = $am ? "AM" : "PM";

        $minutesString = $minutes;
        if ($minutes < 10) {
            $minutesString = "0$minutes";
        }

        return "$hours:$minutesString $ampm";
    }

    public function getUserTimeDescription(User $user) : string {
        $offsetSeconds = TimeHelper::getTimezoneOffsetSecondsFromTimezone($user->getTimezone());
        $offsetMinutes = round($offsetSeconds / 60);

        $userStartMinute = $this->startMinute + $offsetMinutes;
        $userEndMinute = $this->endMinute + $offsetMinutes;

        $cardTitle = '';

        $weekString = $this->weekInterval == 1 ? 'week' : $this->weekInterval . " weeks";

        if ($this->weekday === DaysOfWeek::toId("Any")) {
            // NO SPECIFIC WEEKDAY

            // Convert negatives (like -60 minutes) to positive (23 hours)
            // Convert positives (25 hours) to positive (60 minutes)
            if ($userStartMinute < 0) {
                $userStartMinute += 24 * 60;
            }
            if ($userStartMinute > 24 * 60) {
                $userStartMinute -= 24 * 60;
            }
            if ($userEndMinute < 0) {
                $userEndMinute += 24 * 60;
            }
            if ($userEndMinute > 24 * 60) {
                $userEndMinute -= 24 * 60;
            }

            $fromString = $this->minutesToReadable($userStartMinute);
            $toString = $this->minutesToReadable($userEndMinute);
            $cardTitle = "Every $weekString from $fromString to $toString on any day";
        } else {
            // WEEKDAYS, so must consider negative and its affect on weekdays
            if ($userStartMinute >= 0 && $userEndMinute >= 0 && $userStartMinute < 24*60 && $userEndMinute < 24*60) {
                // Times fall on same weekday
                $fromString = $this->minutesToReadable($userStartMinute);
                $toString = $this->minutesToReadable($userEndMinute);
                $weekdayString = DaysOfWeek::getFromId($this->weekday);
                $cardTitle = "Every $weekString from $fromString to $toString on $weekdayString";
            } else {
                $weekdayId = $this->weekday;

                // Logic for fromString
                $fromString = '';
                if ($userStartMinute < 0) {
                    $fromWeekdayId = DaysOfWeek::getIdOfDayBeforeId($weekdayId);
                    $weekdayName = DaysOfWeek::getFromId($fromWeekdayId);
                    $userStartMinute += 24*60;
                    $fromString = "$weekdayName " . $this->minutesToReadable($userStartMinute);
                } else if ($userStartMinute > 24*60) {
                    $fromWeekdayId = DaysOfWeek::getIdOfDayAfterId($weekdayId);
                    $weekdayName = DaysOfWeek::getFromId($fromWeekdayId);
                    $userStartMinute -= 24*60;
                    $fromString = "$weekdayName " . $this->minutesToReadable($userStartMinute);
                } else {
                    $weekdayName = DaysOfWeek::getFromId($weekdayId);
                    $fromString = "$weekdayName " . $this->minutesToReadable($userStartMinute);
                }
                // Logic for toString
                $toString = '';
                if ($userEndMinute < 0) {
                    $fromWeekdayId = DaysOfWeek::getIdOfDayBeforeId($weekdayId);
                    $weekdayName = DaysOfWeek::getFromId($fromWeekdayId);
                    $userEndMinute += 24*60;
                    $toString = "$weekdayName " . $this->minutesToReadable($userEndMinute);
                } else if ($userEndMinute > 24*60) {
                    $fromWeekdayId = DaysOfWeek::getIdOfDayAfterId($weekdayId);
                    $weekdayName = DaysOfWeek::getFromId($fromWeekdayId);
                    $userEndMinute -= 24*60;
                    $toString = "$weekdayName " . $this->minutesToReadable($userEndMinute);
                } else {
                    $weekdayName = DaysOfWeek::getFromId($weekdayId);
                    $toString = "$weekdayName " . $this->minutesToReadable($userEndMinute);
                }

                $cardTitle = "Every $weekString from $fromString to $toString";
            }
        }
        return $cardTitle;
    }

    public function toHTML($view, User $user) {
        // CONVERT TO LOCAL USER'S TIMEZONE
        /*
        $fromString = $this->minutesToReadable($this->startMinute);
        $toString = $this->minutesToReadable($this->endMinute);
        $weekdayString = "";
        if ($this->weekday !== DaysOfWeek::getId("Any")) {
            $weekdayString = " on " . DaysOfWeek::getFromId($this->weekday);
        }*/
        $cardTitle = $this->getUserTimeDescription($user);
        ?>
        <div class="card schedule-card">
            <div class="card-body">
                <p class="card-title"><?=$cardTitle?></p>
                <a onclick="deleteSchedule(<?=$this->localIndex?>)" href="javascript:void(0);" class="card-link">Delete</a>
            </div>
            <div class="card-footer">
                <span class="badge badge-success">Recurring</span>
            </div>
        </div>
    <?php
    }

    public function setLocalIndex($index)
    {
        $this->localIndex = $index;
    }
}