<?php


namespace MathIKnow;


class Availability {
    public $id, $name;

    /**
     * Availability constructor.
     * @param $id
     * @param $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}