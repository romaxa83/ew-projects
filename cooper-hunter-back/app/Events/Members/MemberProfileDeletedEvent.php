<?php

namespace App\Events\Members;

use App\Contracts\Members\Member;

/**
 * Soft deleted member profile
 */
class MemberProfileDeletedEvent
{
    public function __construct(private Member $member)
    {
    }

    public function getMember(): Member
    {
        return $this->member;
    }
}
