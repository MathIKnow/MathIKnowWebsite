<?php


namespace MathIKnow;


class UserPreferences {
    public $contactMethod;

    public function __construct(string $contactMethod) {
        $this->contactMethod = $contactMethod;
    }
}