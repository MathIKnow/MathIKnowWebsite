<?php


namespace MathIKnow;


class GradeLevel {
    public $id, $name;

    /**
     * GradeLevel constructor.
     * @param $id
     * @param $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}