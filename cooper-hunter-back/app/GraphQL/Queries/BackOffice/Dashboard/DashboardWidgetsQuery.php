<?php

namespace App\GraphQL\Queries\BackOffice\Dashboard;

use App\Collections\Dashboard\Widgets\DashboardWidgetsCollection;
use App\GraphQL\Types\Dashboard\Widgets\DashboardWidgetType;
use App\Services\Dashboard\DashboardService;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class DashboardWidgetsQuery extends BaseQuery
{
    public const NAME = 'dashboardWidgets';

    public function __construct(protected DashboardService $service)
    {
        $this->setAdminGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return DashboardWidgetType::nonNullList();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): DashboardWidgetsCollection {
        return $this->service->widgets($this->user());
    }
}