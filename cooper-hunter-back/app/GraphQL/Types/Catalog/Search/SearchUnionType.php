<?php

namespace App\GraphQL\Types\Catalog\Search;

use App\GraphQL\Types\BaseUnionType;
use App\GraphQL\Types\Catalog\Categories\CategoryRootType;
use App\GraphQL\Types\Catalog\Categories\CategoryType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;

class SearchUnionType extends BaseUnionType
{
    public const NAME = 'SearchUnionType';

    /**
     * @inheritDoc
     */
    public function types(): array
    {
        return [
            CategoryType::type(),
            CategoryRootType::type(),
            ProductType::type(),
        ];
    }

    public function resolveType(Category|Product $value): Type|NullableType
    {
        if ($value instanceof Category) {
            if($value->isRoot()){
                return CategoryRootType::type();
            }
            return CategoryType::type();
        }

        return ProductType::type();
    }
}
