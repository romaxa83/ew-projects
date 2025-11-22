<?php


namespace App\Events\Favourites;


use App\Contracts\Members\Member;
use App\Contracts\Subscriptions\FavouriteSubscriptionEvent;
use App\Enums\Favourites\FavouriteSubscriptionActionEnum;
use App\Models\Catalog\Favourites\Favourite;

class FavouriteCreatedEvent implements FavouriteSubscriptionEvent
{

    public function __construct(private Favourite $favourite)
    {
    }

    public function getMember(): Member
    {
        return $this->favourite->member;
    }

    public function getType(): string
    {
        return $this->favourite->favorable_type;
    }

    public function getFavourite(): ?Favourite
    {
        return $this->favourite;
    }

    public function getAction(): string
    {
        return FavouriteSubscriptionActionEnum::CREATED;
    }
}
