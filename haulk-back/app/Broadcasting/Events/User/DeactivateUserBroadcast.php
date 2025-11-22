<?php

namespace App\Broadcasting\Events\User;

class DeactivateUserBroadcast extends UserBroadcast
{
    public const NAME = 'user.deactivate';

    protected function getName(): string
    {
        return self::NAME;
    }
}
