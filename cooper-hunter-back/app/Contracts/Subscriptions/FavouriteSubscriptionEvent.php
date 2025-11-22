<?php


namespace App\Contracts\Subscriptions;


use App\Contracts\Members\Member;
use App\Models\Catalog\Favourites\Favourite;

interface FavouriteSubscriptionEvent
{
    public function getMember(): Member;

    public function getType(): string;

    public function getFavourite(): ?Favourite;

    public function getAction(): string;
}
