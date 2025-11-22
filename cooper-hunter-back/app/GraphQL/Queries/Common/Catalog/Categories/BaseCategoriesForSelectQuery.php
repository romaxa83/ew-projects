<?php

namespace App\GraphQL\Queries\Common\Catalog\Categories;

use App\GraphQL\Types\Catalog\Categories\CategoryForSelectType;
use App\Models\Catalog\Categories\Category;
use App\Permissions\Catalog\Categories\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class BaseCategoriesForSelectQuery extends BaseQuery
{
    public const NAME = 'categoriesForSelect';


    public function type(): Type
    {
        return CategoryForSelectType::list();
    }

    public function args(): array
    {
        return [
            'with_olmo' => [
                'type' => Type::boolean(),
            ],
            'with_spares' => [
                'type' => Type::boolean(),
            ],
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): array
    {
        return Category::getForSelect(null, $args);
    }
}
