<?php

namespace App\Listeners\Members;

use App\Events\Members\MemberProfileDeletedEvent;
use App\Notifications\Users\MemberAccountDeletedNotification;
use Illuminate\Support\Facades\Notification;

class MemberProfileDeletedListener
{
    public function handle(MemberProfileDeletedEvent $event): void
    {
        $member = $event->getMember();

        Notification::route('mail', (string)$member->getEmail())
            ->notify(new MemberAccountDeletedNotification($member->getName()));
    }
}
