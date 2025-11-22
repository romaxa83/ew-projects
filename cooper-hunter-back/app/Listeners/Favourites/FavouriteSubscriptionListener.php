<?php


namespace App\Listeners\Favourites;


use App\Contracts\Subscriptions\FavouriteSubscriptionEvent;
use App\GraphQL\Subscriptions\FrontOffice\Favourites\FavouriteSubscription;
use App\Models\Technicians\Technician;
use App\Models\Users\User;

class FavouriteSubscriptionListener
{

    public function handle(FavouriteSubscriptionEvent $event): void
    {
        $favourite = $event->getFavourite();

        /**@var Technician|User $member */
        $member = $event->getMember();

        FavouriteSubscription::notify()
            ->toUser($member)
            ->withContext(
                [
                    'favourite_id' => $favourite ? $favourite->favorable_id : null,
                    'favourite_type' => $event->getType(),
                    'action' => $event->getAction(),
                ]
            )
            ->broadcast();
    }
}
