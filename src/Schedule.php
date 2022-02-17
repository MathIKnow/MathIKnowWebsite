<?php


namespace MathIKnow;


interface Schedule {
    public function isRecurring();
    public function getDurationMinutes();
    public function toArray();
    public static function fromArray($array);
    public function toHTML(string $view, User $user);
    public function setLocalIndex($index);
    public function getUserTimeDescription(User $user) : string;
}