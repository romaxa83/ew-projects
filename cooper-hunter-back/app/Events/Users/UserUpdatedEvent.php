<?php

namespace App\Events\Users;

use App\Contracts\Members\Member;
use App\Contracts\Subscriptions\MemberSubscriptionEvent;
use App\Models\Users\User;

class UserUpdatedEvent implements MemberSubscriptionEvent
{
    public function __construct(private User $user)
    {
    }

    public function getMember(): Member
    {
        return $this->user;
    }
}
