<?php


namespace MathIKnow;


class ApplicationStatus {
    public $user, $processing, $need_info, $deferred, $accepted, $decision_date;

    /**
     * ApplicationStatus constructor.
     * @param User $user
     * @param bool $processing
     * @param bool $need_info
     * @param bool $deferred
     * @param bool $accepted
     * @param ?int $decision_date
     */
    public function __construct(User $user, bool $processing, bool $need_info, bool $deferred, bool $accepted, ?int $decision_date)
    {
        $this->user = $user;
        $this->processing = $processing;
        $this->need_info = $need_info;
        $this->deferred = $deferred;
        $this->accepted = $accepted;
        $this->decision_date = $decision_date;
    }
}