<?php


namespace App\Contracts\Subscriptions;


use App\Contracts\Members\Member;

interface MemberSubscriptionEvent
{
    public function getMember(): Member;
}
