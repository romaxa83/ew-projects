<?php

namespace App\GraphQL\Types\Catalog\Favourites;

use App\Contracts\Favourites\Favorable;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\Models\Catalog\Favourites\Favourite;

class FavouriteProductType extends BaseType
{
    public const NAME = 'FavouriteProductType';
    public const MODEL = Favourite::class;

    public function fields(): array
    {
        return [
            'favorable' => [
                /** @see FavouriteProductType::resolveFavorableField() */
                'type' => ProductType::nonNullType(),
            ],
        ];
    }

    protected function resolveFavorableField(Favourite $f): Favorable
    {
        $favorable = $f->favorable;
        $favorable->is_favourite = true;

        return $favorable;
    }
}
