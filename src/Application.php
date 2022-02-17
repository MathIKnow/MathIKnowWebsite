<?php


namespace MathIKnow;


class Application {
    public $user, $grade, $courses, $course_proof_file, $availability, $past_experience;

    /**
     * Application constructor.
     * @param User $user
     * @param GradeLevel $grade
     * @param MathCourse[] $courses
     * @param string|null $course_proof_file
     * @param Availability[] $availability
     * @param string $past_experience
     */
    public function __construct(User $user, GradeLevel $grade, array $courses, ?string $course_proof_file,
                                array $availability, string $past_experience) {
        $this->user = $user;
        $this->grade = $grade;
        $this->courses = $courses;
        $this->course_proof_file = $course_proof_file;
        $this->availability = $availability;
        $this->past_experience = $past_experience;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return GradeLevel
     */
    public function getGrade(): GradeLevel
    {
        return $this->grade;
    }

    /**
     * @return MathCourse[]
     */
    public function getCourses(): array
    {
        return $this->courses;
    }

    public function getCoursesIdArray() : array {
        $array = [];
        foreach ($this->getCourses() as $course) {
            $array[] = $course->id;
        }
        return $array;
    }

    /**
     * @return string
     */
    public function getCourseProofFile(): string
    {
        return $this->course_proof_file;
    }

    public function getCourseProofLink() {
        return "https://mathiknow.com/uploads/" . $this->course_proof_file;
    }

    /**
     * @return Availability[]
     */
    public function getAvailability(): array
    {
        return $this->availability;
    }

    /**
     * @return string
     */
    public function getPastExperience(): string
    {
        return $this->past_experience;
    }


}