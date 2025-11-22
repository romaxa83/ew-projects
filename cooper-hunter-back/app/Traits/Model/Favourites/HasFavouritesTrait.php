<?php

namespace App\Traits\Model\Favourites;

use App\Models\Catalog\Favourites\Favourite;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFavouritesTrait
{
    public function favourites(): MorphMany|Favourite
    {
        return $this->morphMany(Favourite::class, 'member');
    }
}
