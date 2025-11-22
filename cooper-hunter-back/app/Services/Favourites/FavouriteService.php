<?php

namespace App\Services\Favourites;

use App\Contracts\Favourites\Favorable;
use App\Contracts\Members\Member;
use App\Events\Favourites\FavouriteDeletedEvent;
use App\Models\Catalog\Favourites\Favourite;

class FavouriteService
{
    public function add(Member $member, Favorable $favorable): Favourite
    {
        return Favourite::query()
            ->firstOrCreate(
                [
                    'member_type' => $member->getMorphType(),
                    'member_id' => $member->getId(),
                    'favorable_type' => $favorable->getFavorableType(),
                    'favorable_id' => $favorable->getId(),
                ],
                [
                    'created_at' => now(),
                ]
            );
    }

    public function remove(Member $member, Favorable $favorable): void
    {
        $favorite = Favourite::query()
            ->where(
                [
                    'member_type' => $member->getMorphType(),
                    'member_id' => $member->getId(),
                    'favorable_type' => $favorable->getFavorableType(),
                    'favorable_id' => $favorable->getId(),
                ]
            )
            ->first();

        if (!$favorite) {
            return;
        }

        $favorite->delete();
    }

    public function removeAll(Member $member, Favorable $favorable): void
    {
        Favourite::query()
            ->where(
                [
                    'member_type' => $member->getMorphType(),
                    'member_id' => $member->getId(),
                    'favorable_type' => $favorable->getFavorableType(),
                ]
            )
            ->delete();

        event(new FavouriteDeletedEvent(type: $favorable->getFavorableType(), member: $member));
    }
}
