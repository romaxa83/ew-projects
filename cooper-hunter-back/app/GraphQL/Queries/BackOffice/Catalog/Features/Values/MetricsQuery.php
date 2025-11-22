<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Features\Values;

use App\GraphQL\Types\Catalog\Features\Values\MetricType;
use App\Models\Catalog\Features\Metric;
use App\Permissions\Catalog\Features\Values\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class MetricsQuery extends BaseQuery
{
    public const NAME = 'featureMetrics';
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return MetricType::nonNullList();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return Metric::query()
            ->orderBy('id')
            ->get();
    }
}


