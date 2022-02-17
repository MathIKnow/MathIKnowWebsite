<?php


namespace MathIKnow;


class RequestWeighAlgorithm {
    public $request, $tutor;
    // Higher = more relevant
    /*
     * closer to the top =
        - you claimed it
        - you contacted them
        - it aligns with your courses
        - it aligns with your time preferences
        - older requests (IMPLEMENT IN SORTER)
     */

    /**
     * RequestWeighAlgorithm constructor.
     * @param TutorRequest $request
     * @param User $tutor
     */
    public function __construct(TutorRequest $request, User $tutor)
    {
        $this->request = $request;
        $this->tutor = $tutor;
    }

    public function weigh_claim() : float {
        return in_array($this->tutor->id, $this->request->getTutorsClaimedIds()) ? 1.0 : 0.0;
    }

    public function weigh_contact() : float {
        return in_array($this->tutor->id, $this->request->getTutorsReachedOutIds()) ? 1.0 : 0.0;
    }

    public function weigh_course() : float {
        $requestCourse = $this->request->getKnownMathCourse();
        if ($requestCourse == null) {
            return 0.0;
        }
        $tutorCourses = ApplicationDatabase::getApplication($this->tutor)->getCourses();
        foreach ($tutorCourses as $tutorCourse) {
            if ($tutorCourse->id === $requestCourse->id) {
                // Tutor tutors the course of the request!
                return 1.0;
            }
        }
        return 0.0;
    }

    public function weight_archived() : float {
        return $this->request->archived ? 0.0  : 1.1;
    }

    public function calculateWeight() {
        return 1 * $this->weigh_course() + 10 * $this->weigh_contact() + 100 * $this->weigh_claim() + 1000 * $this->weight_archived();
    }
}