<?php


namespace App\Events\Favourites;


use App\Contracts\Members\Member;
use App\Contracts\Subscriptions\FavouriteSubscriptionEvent;
use App\Enums\Favourites\FavouriteSubscriptionActionEnum;
use App\Models\Catalog\Favourites\Favourite;

class FavouriteDeletedEvent implements FavouriteSubscriptionEvent
{

    public function __construct(
        private ?Favourite $favourite = null,
        private ?string $type = null,
        private ?Member $member = null
    ) {
    }

    public function getMember(): Member
    {
        return $this->member ?? $this->favourite->member;
    }

    public function getType(): string
    {
        return $this->type ?? $this->favourite->favorable_type;
    }

    public function getFavourite(): ?Favourite
    {
        return $this->favourite;
    }

    public function getAction(): string
    {
        return $this->favourite ? FavouriteSubscriptionActionEnum::DELETED : FavouriteSubscriptionActionEnum::DELETED_ALL;
    }
}
