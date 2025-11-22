<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Solutions;

use App\GraphQL\Types\Catalog\Solutions\SolutionSeriesType;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Permissions\Catalog\Solutions\SolutionReadPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class SolutionSeriesListQuery extends BaseQuery
{
    public const NAME = 'solutionSeriesList';
    public const PERMISSION = SolutionReadPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return SolutionSeriesType::nonNullList();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return SolutionSeries::query()
            ->filter($args)
            ->get();
    }
}
