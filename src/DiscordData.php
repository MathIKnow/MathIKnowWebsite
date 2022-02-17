<?php


namespace MathIKnow;


class DiscordData {
    public $userId, $discordId, $username, $discriminator;

    /**
     * DiscordData constructor.
     * @param $userId
     * @param $discordId
     * @param $username
     * @param $discriminator
     */
    public function __construct($userId, $discordId, $username, $discriminator)
    {
        $this->userId = $userId;
        $this->discordId = $discordId;
        $this->username = $username;
        $this->discriminator = $discriminator;
    }

    public function getFullUsername() {
        return $this->username . "#" . $this->discriminator;
    }
}