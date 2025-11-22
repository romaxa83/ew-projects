<?php

namespace App\GraphQL\Queries\Common\Catalog\UnitType;

use App\GraphQL\Types\Catalog\Products\UnitTypeType;
use App\Models\Catalog\Products\UnitType;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseUnitTypesQuery extends BaseQuery
{
    public const NAME = 'unitTypes';

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return UnitTypeType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?Collection
    {
        return UnitType::query()->get();
    }
}

