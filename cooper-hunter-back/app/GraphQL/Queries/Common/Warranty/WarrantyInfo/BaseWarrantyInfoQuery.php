<?php

namespace App\GraphQL\Queries\Common\Warranty\WarrantyInfo;

use App\GraphQL\Types\Warranty\WarrantyInfoType\WarrantyInfoType;
use App\Models\Warranty\WarrantyInfo\WarrantyInfo;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseWarrantyInfoQuery extends BaseQuery
{
    public const NAME = 'warrantyInfo';

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return WarrantyInfoType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?WarrantyInfo {
        return WarrantyInfo::query()
            ->with($fields->getRelations())
            ->first();
    }
}
