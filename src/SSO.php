<?php

namespace MathIKnow;

class SSO {
    private static $FORUM_SSO_SECRET = '';

    private static function createSig($base64Payload) {
        return hash_hmac('sha256', $base64Payload, self::$FORUM_SSO_SECRET, false);
    }

    public static function verifyPayload($ssoBase64, $sig) {
        return self::createSig($ssoBase64) == $sig;
    }

    public static function getNonce($ssoBase64) {
        // This will start with nonce=, so we will parse_str it to get stuff after nonce=
        $rawPayload = base64_decode($ssoBase64);
        $parsed = [];
        parse_str($rawPayload, $parsed);
        return isset($parsed['nonce']) ? $parsed['nonce'] : null;
    }

    public static function getRedirectURLToForum($nonce, User $user) {
        // Groups
        $addGroups = [];
        $removeGroups = [];
        self::setupGroup('student', 'students', $user, $addGroups, $removeGroups);
        self::setupGroup('tutor', 'tutors', $user, $addGroups, $removeGroups);
        self::setupGroup('tutor_approved', 'tutors_approved', $user, $addGroups, $removeGroups);

        $rawPayload = http_build_query([
           'nonce' => $nonce,
           'email' => $user->email,
           'external_id' => $user->id,
           'username' => $user->username,
           'name' => $user->username,
           'add_groups' => implode(',', $addGroups),
           'remove_groups' => implode(',', $removeGroups)
        ]);
        $base64Payload = base64_encode($rawPayload);
        $sig = self::createSig($base64Payload);
        $urlEncodedPayload = urlencode($base64Payload);

        return "https://forums.mathiknow.com/session/sso_login?sso=$urlEncodedPayload&sig=$sig";
    }

    private static function setupGroup(string $group, string $forumGroup, User $user, array &$addGroups, array &$removeGroups) {
        if ($user->isInGroup($group)) {
            $addGroups[] = $forumGroup;
        } else {
            $removeGroups[] = $forumGroup;
        }
    }
}
