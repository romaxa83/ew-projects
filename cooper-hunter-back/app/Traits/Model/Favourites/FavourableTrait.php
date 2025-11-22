<?php

namespace App\Traits\Model\Favourites;

use App\Contracts\Members\HasFavourites;
use App\Contracts\Roles\HasGuardUser;
use App\Models\Catalog\Favourites\Favourite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

/**
 * @see FavourableTrait::scopeAddIsFavourite()
 * @method Builder|static addIsFavourite(HasFavourites|HasGuardUser $favorable)
 */
trait FavourableTrait
{
    public function scopeAddIsFavourite(Builder|self $b, HasFavourites|HasGuardUser|null $favorable): void
    {
        if (!$favorable) {
            return;
        }

        $b->leftJoin(
            Favourite::TABLE,
            fn(JoinClause $j) => $j
                ->on(Favourite::TABLE . '.favorable_id', '=', static::TABLE . '.id')
                ->where('favorable_type', static::MORPH_NAME)
                ->where('member_id', $favorable->getId())
                ->where('member_type', $favorable->getMorphType())
        )
            ->addSelect(Favourite::TABLE . '.id as is_favourite')
            ->groupBy('is_favourite');
    }
}
