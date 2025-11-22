<?php

namespace App\GraphQL\Queries\FrontOffice\About;

use App\GraphQL\Types\Catalog\Features\Specifications\SpecificationType;
use App\Models\Catalog\Features\Specification;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class SpecificationsQuery extends BaseQuery
{
    public const NAME = 'specifications';

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return SpecificationType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return Specification::query()
            ->where('active', true)
            ->latest('sort')
            ->get();
    }
}
