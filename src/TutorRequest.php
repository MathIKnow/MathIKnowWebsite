<?php


namespace MathIKnow;


class TutorRequest {
    public $id, $user, $timestamp, $name, $description, $schedules, $tutorsReachedOut, $tutorsClaimed, $mathCourse, $archived;

    /**
     * TutorRequest constructor.
     * @param int $id
     * @param User $user
     * @param int $timestamp
     * @param string $name
     * @param string $description
     * @param Schedule[] $schedules
     * @param User[] $tutorsReachedOut
     * @param User[] $tutorsClaimed
     * @param string $mathCourse
     * @param $archived
     */
    public function __construct(int $id, User $user, int $timestamp, string $name, string $description,
                                array $schedules, array $tutorsReachedOut, array $tutorsClaimed, string $mathCourse,
                                $archived) {
        $this->id = $id;
        $this->user = $user;
        $this->timestamp = $timestamp;
        $this->name = $name;
        $this->description = $description;
        $this->schedules = $schedules;
        $this->tutorsReachedOut = $tutorsReachedOut;
        $this->tutorsClaimed = $tutorsClaimed;
        $this->mathCourse = $mathCourse;
        $this->archived = $archived;
    }

    public function getTutorsReachedOutIds() {
        $ids = [];
        foreach ($this->tutorsReachedOut as $tutor) {
            $ids[] = $tutor->id;
        }
        return $ids;
    }

    public function getTutorsClaimedIds() {
        $ids = [];
        foreach ($this->tutorsClaimed as $tutor) {
            $ids[] = $tutor->id;
        }
        return $ids;
    }

    public function getMathCourseName() {
        if (Utilities::isInt($this->mathCourse)) {
            $id = $this->mathCourse;
            return MathCourses::getCourseFromId($id)->name;
        }
        return $this->mathCourse;
    }

    public function getKnownMathCourse() : ?MathCourse {
        if (Utilities::isInt($this->mathCourse)) {
            $id = $this->mathCourse;
            return MathCourses::getCourseFromId($id);
        }
        return null;
    }

    public function echoStudentPortalHTML(User $student) {
        $footerHTML = '';
        foreach ($this->schedules as $schedule) {
            $inside = $schedule->getUserTimeDescription($student);
            $badge = '';
            if ($this->archived) {
                $badge = 'badge-secondary';
            } else {
                if ($schedule instanceof InstanceSchedule) {
                    $badge = 'badge-primary';
                } else if ($schedule instanceof RecurringSchedule) {
                    $badge = 'badge-success';
                } else if ($schedule instanceof FlexibleSchedule) {
                    $badge = 'badge-danger';
                }
            }
            $footerHTML .= "<span class=\"badge text-wrap badge-spaced $badge\">$inside</span>";
        }
        if ($this->mathCourse) {
            $course = $this->getMathCourseName();
            $badge = $this->archived ? "badge-secondary" : 'badge-info';
            $footerHTML .= "<span class=\"badge text-wrap badge-spaced $badge\">Course: $course</span>";
        }
        if ($this->archived) {
            $footerHTML .= '<span class="badge text-wrap badge-spaced badge-warning">Archived</span>';
        }
        ?>
        <div class="card schedule-card">
            <div class="card-body">
                <h2 class="card-title<?=$this->archived ? ' text-muted' : ''?>"><?=$this->name?></h2>
                <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>Request Date:</b> <?=TimeHelper::formatForUser($this->timestamp, $student)?></p>
                <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>Description:</b> <?=Utilities::ellipsis($this->description, 500)?></p>
                <?php
                $reachedOutTutors = $this->tutorsReachedOut;
                $claimedTutors = $this->tutorsClaimed;
                if ($reachedOutTutors && sizeof($reachedOutTutors) >= 1) {
                    $reachedOutNames = [];
                    foreach ($reachedOutTutors as $tutor) {
                        $reachedOutNames[] = $tutor->username;
                    }
                    ?>
                    <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>Tutors that have contacted you:</b> <?=implode(', ', $reachedOutNames)?></p>
                    <?php
                }
                if ($claimedTutors && sizeof($claimedTutors) >= 1) {
                    $claimedTutorsNames = [];
                    foreach ($claimedTutors as $tutor) {
                        $claimedTutorsNames[] = $tutor->username;
                    }
                    ?>
                    <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>Tutors that have claimed this request:</b> <?=implode(', ', $claimedTutorsNames)?></p>
                    <?php
                }
                ?>
            </div>
            <div class="card-footer">
                <h4><?=$footerHTML?></h4>
                <?php
                if (!$this->archived) {
                    ?>
                    <a href="/student-portal/archive-request.php?request_id=<?=$this->id?>" class="card-link">Archive</a>
                    <?php
                } else {
                    ?>
                    <a href="/student-portal/unarchive-request.php?request_id=<?=$this->id?>" class="card-link">Unarchive</a>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }

    public function echoTutorPortalHTML(User $tutor) {
        $footerHTML = '';
        foreach ($this->schedules as $schedule) {
            $inside = $schedule->getUserTimeDescription($tutor);
            $badge = '';
            if ($this->archived) {
                $badge = 'badge-secondary';
            } else {
                if ($schedule instanceof InstanceSchedule) {
                    $badge = 'badge-primary';
                } else if ($schedule instanceof RecurringSchedule) {
                    $badge = 'badge-success';
                } else if ($schedule instanceof FlexibleSchedule) {
                    $badge = 'badge-danger';
                }
            }
            $footerHTML .= "<span class=\"badge text-wrap badge-spaced $badge\">$inside</span>";
        }
        if ($this->mathCourse) {
            $course = $this->getMathCourseName();
            $badge = $this->archived ? "badge-secondary" : 'badge-info';
            $footerHTML .= "<span class=\"badge text-wrap badge-spaced $badge\">Course: $course</span>";
        }
        if ($this->archived) {
            $footerHTML .= '<span class="badge text-wrap badge-spaced badge-warning">Archived</span>';
        }

        $userString = $this->user->username;

        $preferredContact = 'Email';
        $preferences = UserDatabase::getPreferences($this->user);
        if ($preferences) {
            $preferredContact = $preferences->contactMethod;
        }

        ?>
        <div class="card schedule-card">
            <div class="card-body">
                <h2 class="card-title<?=$this->archived ? ' text-muted' : ''?>"><?=$this->name?></h2>
                <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>Request Date:</b> <?=TimeHelper::formatForUser($this->timestamp, $tutor)?></p>
                <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>User:</b> <?=$userString?> (<?=$this->user->email?>)</p>
                <?php
                $discordData = UserDiscordDatabase::getDiscordData($this->user);
                if ($discordData) {
                ?>
                    <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>Discord: </b><span style="background-color: rgb(191,191,191);"><?=$discordData->getFullUsername()?></span></p>
                <?php
                }
                ?>
                <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>Preferred Contact Method:</b> <?=$preferredContact?></p>
                <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>Description:</b> <?=Utilities::ellipsis($this->description, 500)?></p>
                <?php
                $reachedOutTutors = $this->tutorsReachedOut;
                $claimedTutors = $this->tutorsClaimed;
                if ($reachedOutTutors && sizeof($reachedOutTutors) >= 1) {
                    $reachedOutNames = [];
                    foreach ($reachedOutTutors as $tutorA) {
                        $reachedOutNames[] = $tutorA->username;
                    }
                    ?>
                    <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>Tutors that have contacted the student:</b> <?=implode(', ', $reachedOutNames)?></p>
                    <?php
                }
                if ($claimedTutors && sizeof($claimedTutors) >= 1) {
                    $claimedTutorsNames = [];
                    foreach ($claimedTutors as $tutorA) {
                        $claimedTutorsNames[] = $tutorA->username;
                    }
                    ?>
                    <p class="card-text<?=$this->archived ? ' text-muted' : ''?>"><b>Tutors that have claimed this request:</b> <?=implode(', ', $claimedTutorsNames)?></p>
                    <?php
                }
                ?>
            </div>
            <div class="card-footer">
                <h4><?=$footerHTML?></h4>
                <?php
                $contacted = in_array($tutor->id, $this->getTutorsReachedOutIds()) === TRUE;
                $claimed = in_array($tutor->id, $this->getTutorsClaimedIds()) === TRUE;
                ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input action-checkbox" type="checkbox" id="contacted-<?=$this->id?>" value="contacted-<?=$this->id?>"<?=$contacted ? " checked" : ""?>>
                    <label class="form-check-label" for="contacted-<?=$this->id?>">I contacted them</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input action-checkbox" type="checkbox" id="claimed-<?=$this->id?>" value="claimed-<?=$this->id?>"<?=$claimed ? " checked" : ""?>>
                    <label class="form-check-label" for="claimed-<?=$this->id?>">I claim this request</label>
                </div>
            </div>
        </div>
        <?php
    }
}