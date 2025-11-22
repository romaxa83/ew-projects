<?php

namespace App\Listeners\Members;

use App\Contracts\Subscriptions\MemberSubscriptionEvent;
use App\GraphQL\Subscriptions\FrontOffice\Members\MemberSubscription;
use App\Models\Technicians\Technician;
use App\Models\Users\User;

class MemberSubscriptionListener
{
    public function handle(MemberSubscriptionEvent $event): void
    {
        /**@var Technician|User $member */
        $member = $event->getMember();

        MemberSubscription::notify()
            ->toUser($member)
            ->withContext(
                [
                    'member' => $member->getId(),
                    'type' => $member->getMorphType()
                ]
            )
            ->broadcast();
    }
}
