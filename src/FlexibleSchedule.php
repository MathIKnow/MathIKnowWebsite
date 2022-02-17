<?php


namespace MathIKnow;


class FlexibleSchedule implements Schedule {
    public static $validDurations = ["On Demand Messaging" => "On Demand Messaging", 15 => "15 Minutes",
        30 => "30 Minutes", 60 => "1 Hour", 90 => "1 Hour 30 Minutes", 120 => "2 Hours", 150 => "2 Hours 30 Minutes",
        180 => "3 Hours", 210 => "3 Hours 30 Minutes", 240 => "4 Hours"];
    public $duration, $localIndex = -1;

    public function __construct($duration) {
        $this->duration = $duration;
    }

    public function isRecurring() {
        return true;
    }

    public function getDurationMinutes() {
        return Utilities::isInt($this->duration) ? $this->duration : 60;
    }

    public function toArray() {
        return [
            'type' => 'flexible',
            'duration' => $this->duration
        ];
    }

    public static function fromArray($array) {
        return new FlexibleSchedule($array['duration']);
    }

    public function getUserTimeDescription(User $user) : string {
        $cardTitle = '';
        if ($this->duration === "On Demand Messaging") {
            $cardTitle = "On Demand Messaging";
        } else {
            $cardTitle = "Flexible with sessions of about " . self::$validDurations[$this->duration];
        }
        return $cardTitle;
    }

    public function toHTML($view, $user) {
        $cardTitle = $this->getUserTimeDescription($user);
        ?>
        <div class="card schedule-card">
            <div class="card-body">
                <p class="card-title"><?=$cardTitle?></p>
                <a onclick="deleteSchedule(<?=$this->localIndex?>)" href="javascript:void(0);" class="card-link">Delete</a>
            </div>
            <div class="card-footer">
                <span class="badge badge-danger">Flexible</span>
            </div>
        </div>
        <?php
    }

    public function setLocalIndex($index)
    {
        $this->localIndex = $index;
    }
}