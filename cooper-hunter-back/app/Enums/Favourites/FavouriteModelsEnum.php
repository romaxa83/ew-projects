<?php

namespace App\Enums\Favourites;

use App\Models\Catalog\Products\Product;
use Core\Enums\BaseEnum;

class FavouriteModelsEnum extends BaseEnum
{
    public const PRODUCT = Product::class;

    public static function getValues(): array
    {
        return static::getKeys();
    }
}
