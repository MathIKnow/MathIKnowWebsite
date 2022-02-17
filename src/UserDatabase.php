<?php

namespace MathIKnow;

use DB;

Database::connect();

class UserDatabase {
    public static $loginTokenExpirationSeconds = 60 * 24 * 60 * 60; // Two months
    private static $mathCoursesMap = [];
    private static $userCache = [];

    public static function getIdByUsername($username) {
        $row = DB::queryFirstRow('SELECT `id` FROM `users` WHERE `username`=%s', $username);
        if ($row == null) {
            return null;
        }
        return $row['id'];
    }

    public static function doesUsernameExist($username) {
        return self::getIdByUsername($username) != null;
    }

    public static function getIdByEmail($email) {
        $row = DB::queryFirstRow("SELECT `id` FROM `users` WHERE `email`=%s", $email);
        if ($row == null) {
            return null;
        }
        return $row['id'];
    }

    public static function doesEmailExist($email) {
        return self::getIdByEmail($email) != null;
    }

    public static function createUser($data) {
        DB::insert('users', $data);
        return DB::insertId();
    }

    public static function updateGroups($id, $student, $tutor) {
        DB::insertUpdate('user_groups', [
           'id' => $id,
           'student' =>  $student,
           'tutor' => $tutor
        ]);
    }

    /**
     * @param $id
     * @return array Array where keys are group names and values are booleans saying whether they're in the group or not
     */
    public static function getGroups($id) {
        $row = DB::queryFirstRow("SELECT * FROM `user_groups` WHERE `id`=%i", $id);
        if ($row == null) {
            return [];
        }
        unset($row['id']);
        return $row;
    }

    public static function isInGroup($id, $groupDBName) {
        $groups = self::getGroups($id);
        return isset($groups[$groupDBName]) && $groups[$groupDBName];
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function sendConfirmationEmail($id, $email) {
        $token = Utilities::generateToken(32);
        DB::insert('user_verify',[
            'id' => $id,
            'token' => $token
        ]);
        $link = "https://mathiknow.com/register/verify.php?token=$token";
        Email::sendHTMLMail($email, 'Please verify your MathIKnow account',
            'Please use the link below to verify your account: <br>'
            . "<a href='$link'>$link</a>",
        "Please verify your account with this link: $link");
    }

    public static function verifyWithToken($token) {
        $row = DB::queryFirstRow("SELECT `id` FROM `user_verify` WHERE `token`=%s", $token);
        if ($row == null) {
            return false;
        }

        $userId = $row['id'];

        $row = DB::queryFirstRow("SELECT `username`, `verified` FROM `users` WHERE `id`=%i", $userId);
        if ($row == null) {
            return false;
        }

        $username = $row['username'];
        $verified = $row['verified'];

        if ($verified) {
            return false;
        }

        DB::update('users', ['verified' => true], "`id`=%i", $userId);
        DB::delete('user_verify', '`id`=%i', $userId);
        return $username;
    }

    public static function createFromDatabase($row) {
        $id = $row['id'];
        return User::createFromDatabase($row, self::getGroups($id));
    }

    public static function getUserFromLogin($identifier, $password) {
        $hashedPassword = self::hashPassword($password);
        $row = DB::queryFirstRow("SELECT * FROM `users` WHERE `username`=%s OR `email`=%s", $identifier, $identifier);
        if ($row == null) {
            return null;
        }
        $storedHash = $row['password'];
        if ($hashedPassword != $storedHash) {
            if (!password_verify($password, $storedHash)) {
                return null;
            }
        }

        return self::createFromDatabase($row);
    }

    public static function getUserFromId($userId) {
        if (isset(self::$userCache[$userId])) {
            return self::$userCache[$userId];
        }
        $row = DB::queryFirstRow("SELECT * FROM `users` WHERE `id`=%i", $userId);
        if ($row == null) {
            return null;
        }

        $user = self::createFromDatabase($row);
        self::$userCache[$userId] = $user;
        return $user;
    }

    public static function deleteExpiredTokens($userId) {
        $oldestPossibleCreation = TimeHelper::getUTCTimestamp() - self::$loginTokenExpirationSeconds;
        DB::delete('user_tokens', '`id`=%i AND `creation_date`<%i', $userId, $oldestPossibleCreation);
    }

    public static function createToken($userId) {
        $token = Utilities::generateToken(64);
        DB::insert('user_tokens', [
            'id' => $userId,
            'token' => $token,
            'ip' => Utilities::getIP(),
            'creation_date' => TimeHelper::getUTCTimestamp(),
            'user_agent' => Utilities::cutToLength(Utilities::getUserAgent(), 200)
        ]);
        return $token;
    }

    public static function getUserFromToken($token) {
        self::deleteExpiredTokens($token);
        $row = DB::queryFirstRow("SELECT `id` FROM `user_tokens` WHERE `token`=%s", $token);
        if ($row == null) {
            return null;
        }

        $userId = $row['id'];
        return self::getUserFromId($userId);
    }

    public static function getUserFromBrowser() {
        if (isset($_COOKIE['login_token'])) {
            $token = $_COOKIE['login_token'];
            $user = null;
            if ($token == null || ($user = self::getUserFromToken($token)) == null) {
                // Delete token
                Utilities::deleteCookie('login_token');
            }
            return $user;
        }
        return null;
    }

    public static function deleteToken($token) {
        DB::delete('user_tokens', '`token`=%s', $token);
    }

    /**
     * @param User $user
     * @return MathCourse[]
     */
    public static function getMathCourses(User $user) : array {
        $id = $user->id;
        if (array_key_exists($id, self::$mathCoursesMap)) {
            return self::$mathCoursesMap[$id];
        }
        $row = DB::queryFirstRow("SELECT `math_courses` FROM `student_courses` WHERE `user_id`=%i", $id);
        if ($row == null) {
            return [];
        }
        $ids = $row['math_courses'];
        $courses = MathCourses::getFromIdArray(Database::expandList($ids));
        self::$mathCoursesMap[$id] = $courses;
        return $courses;
    }

    /**
     * @param User $user
     * @param MathCourse[] $courses
     */
    public static function setMathCourses(User $user, array $courses) {
        DB::insertUpdate('student_courses', [
            'user_id' => $user->id,
            'math_courses' => Database::joinList(MathCourses::toIdArray($courses))
        ]);
    }

    public static function getPreferences(User $user) : UserPreferences {
        $id = $user->id;
        $row = DB::queryFirstRow("SELECT * FROM `student_preferences` WHERE `user_id`=%i", $id);
        if ($row == null) {
            // DEFAULT PREFERENCES
            return new UserPreferences('Email');
        }
        return new UserPreferences($row['contact_method']);
    }

    public static function setPreferences(User $user, UserPreferences $preferences) {
        DB::insertUpdate('student_preferences', [
            'user_id' => $user->id,
            'contact_method' => $preferences->contactMethod
        ]);
    }
}