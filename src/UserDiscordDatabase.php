<?php


namespace MathIKnow;

use DB;
use Discord\OAuth\Discord;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

Database::connect();


class UserDiscordDatabase {
    private static $provider = null;
    private static $refreshBeforeDuration = 24 * 60 * 60 /* SECONDS IN ONE DAY */;
    private static $refreshDiscordDataDuration = 60 * 60 /* 1 hour */;

    public static function getProvider() {
        if (self::$provider == null) {
            self::$provider = new Discord([
                'clientId'     => '',
                'clientSecret' => '',
                'redirectUri'  => 'https://mathiknow.com/settings/link-discord/redirect.php',
            ]);
        }
        return self::$provider;
    }

    public static function getAuthorizationUrl() : string{
        return self::getProvider()->getAuthorizationUrl(['scope' => ['identify', 'email', 'guilds.join']]) ;
    }

    public static function getUserToken(User $user) : ?AccessToken {
        $provider = self::getProvider();

        $row = DB::queryFirstRow('SELECT * FROM `users_discord` WHERE `user_id`=%i', $user->id);
        if ($row == null) {
            return null;
        }
        try {
            $data = [
                'access_token' => $row['access_token'],
                'refresh_token' => $row['refresh_token'],
            ];
            if (isset($row['resource_owner_id']) && !empty($row['resource_owner_id'])) {
                $data['resource_owner_id'] = $row['resource_owner_id'];
            }
            $accessToken = new AccessToken($data);

            $expirationTimestamp = $row['expiration_timestamp'];

            if (($expirationTimestamp - TimeHelper::getUTCTimestamp()) <= self::$refreshBeforeDuration) {
                // Time to refresh
                $accessToken = $refresh = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $accessToken->getRefreshToken(),
                ]);
                self::insertToken($user, $accessToken);
            }
            return $accessToken;
        } catch (\Exception $e) {
            error_log($e);
            DB::delete('users_discord', '`user_id`=%i', $user->id);
            return null;
        }
    }

    public static function getTokenFromCode($code) : AccessToken {
        return self::getProvider()->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    public static function insertToken(User $user, AccessToken $token) {
        $timestamp = TimeHelper::getUTCTimestamp();
        DB::insertUpdate('users_discord', [
            'user_id' => $user->id,
            'access_token' => $token->getToken(),
            'refresh_token' => $token->getRefreshToken(),
            'resource_owner_id' => $token->getResourceOwnerId(),
            'timestamp' => $timestamp,
            'expiration_timestamp' => $token->getExpires()
        ]);
    }

    /** @noinspection PhpUndefinedFieldInspection */
    public static function updateDiscordData(\Discord\OAuth\Parts\User $userObject, User $user) {
        DB::update('users_discord', [
            'discord_last_updated' => TimeHelper::getUTCTimestamp(),
            'discord_id' => $userObject->getId(),
            'username' => $userObject->username,
            'discriminator' => $userObject->discriminator
        ], '`user_id`=%i', $user->id);
    }

    public static function getUserObject(AccessToken $token, User $user) : ?\Discord\OAuth\Parts\User {
        try {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return self::getProvider()->getResourceOwner($token);
        } catch (\Exception $e) {
            error_log($e);
            DB::delete('users_discord', '`user_id`=%i', $user->id);
        }
        return null;
    }

    public static function getDiscordData(User $user) : ?DiscordData {
        $row = DB::queryFirstRow('SELECT * FROM `users_discord` WHERE `user_id`=%i', $user->id);
        if ($row == null) {
            return null;
        }
        $lastUpdatedDiscord = $row['discord_last_updated'] ?? 0;
        if ((TimeHelper::getUTCTimestamp() - $lastUpdatedDiscord) >= self::$refreshDiscordDataDuration) {
            self::updateDiscordData(self::getUserObject(self::getUserToken($user), $user), $user);
        }
        $row = DB::queryFirstRow('SELECT * FROM `users_discord` WHERE `user_id`=%i', $user->id);
        if ($row == null) {
            return null;
        }
        return new DiscordData($row['user_id'], $row['discord_id'], $row['username'], $row['discriminator']);
    }
}