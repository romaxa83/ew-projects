<?php

namespace App\Contracts\Members;

use App\Models\Catalog\Favourites\Favourite;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasFavourites
{
    public function favourites(): MorphMany|Favourite;
}
