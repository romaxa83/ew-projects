<?php

namespace App\GraphQL\Queries\FrontOffice\HomePage;

use App\GraphQL\Queries\Common\Catalog\Categories\BaseCategoriesQuery;
use App\GraphQL\Types\Catalog\Categories\CategoryType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class HomeCategoriesQuery extends BaseCategoriesQuery
{
    public const NAME = 'homeCategories';

    public function type(): Type
    {
        return CategoryType::list();
    }

    public function args(): array
    {
        return [];
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->getQuery($fields, $args)
            ->where('main', true)
            ->where('active', true)
            ->latest('sort')
            ->get();
    }
}
