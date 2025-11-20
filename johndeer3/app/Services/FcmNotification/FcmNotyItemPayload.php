<?php

namespace App\Services\FcmNotification;

class FcmNotyItemPayload
{
    private $fcmToken;
    private $userId;
    private $role;
    private $messagePayload;

    public function __construct(?string $token, string $role, $userId,FcmMessagePayload $message)
    {
        $this->userId = $userId;
        $this->fcmToken = $token;
        $this->role = $role;
        $this->messagePayload = $message;
    }

    public function getFcmToken(): ?string
    {
        return $this->fcmToken;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getMessagePayload(): FcmMessagePayload
    {
        return $this->messagePayload;
    }
}
