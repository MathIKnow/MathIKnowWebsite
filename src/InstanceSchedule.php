<?php


namespace MathIKnow;


class InstanceSchedule implements Schedule {
    public $start_timestamp, $end_timestamp, $localIndex = -1;

    public function __construct($start_timestamp, $end_timestamp)
    {
        $this->start_timestamp = $start_timestamp;
        $this->end_timestamp = $end_timestamp;
    }

    public function isRecurring() {
        return false;
    }

    public function getDurationMinutes() {
        return round(($this->end_timestamp - $this->start_timestamp) / 60);
    }

    public function getStartTimestamp() {
        return $this->start_timestamp;
    }

    public function getEndTimestamp() {
        return $this->end_timestamp;
    }

    public function toArray() {
        return [
            'type' => 'instance',
            'start_timestamp' => $this->start_timestamp,
            'end_timestamp' => $this->end_timestamp
        ];
    }

    public static function fromArray($array) {
        return new InstanceSchedule($array['start_timestamp'], $array['end_timestamp']);
    }

    public function getUserTimeDescription($user) : string {
        $from = TimeHelper::formatForUser($this->start_timestamp, $user);
        $to = TimeHelper::formatForUser($this->end_timestamp, $user);
        return "$from to $to";
    }

    public function toHTML($view, $user) {
        ?>
        <div class="card schedule-card">
            <div class="card-body">
                <p class="card-title">From <?=$this->getUserTimeDescription($user)?></p>
                <a onclick="deleteSchedule(<?=$this->localIndex?>)" href="javascript:void(0);" class="card-link">Delete</a>
            </div>
            <div class="card-footer">
                <span class="badge badge-primary">Specific Instance</span>
            </div>
        </div>
        <?php
    }

    public function setLocalIndex($index)
    {
        $this->localIndex = $index;
    }
}