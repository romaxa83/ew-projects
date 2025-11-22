<?php

namespace App\Broadcasting\Events\User;

class ActivateUserBroadcast extends UserBroadcast
{
    public const NAME = 'user.activate';

    protected function getName(): string
    {
        return self::NAME;
    }
}
