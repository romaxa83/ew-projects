<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Categories;

use App\GraphQL\Queries\Common\Catalog\Categories\BaseCategoriesQuery;
use App\GraphQL\Types\Catalog\Categories\CategoryType;
use App\Permissions\Catalog\Categories\ListPermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class CategoriesQuery extends BaseCategoriesQuery
{
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return CategoryType::list();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->getQuery($fields, $args)->oldest('sort')->get();
    }
}
