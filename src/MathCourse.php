<?php


namespace MathIKnow;


class MathCourse {
    public $id, $prefixed_id, $name;

    /**
     * MathCourse constructor.
     * @param $id
     * @param $prefixed_id
     * @param $name
     */
    public function __construct($id, $prefixed_id, $name)
    {
        $this->id = $id;
        $this->prefixed_id = $prefixed_id;
        $this->name = $name;
    }
}