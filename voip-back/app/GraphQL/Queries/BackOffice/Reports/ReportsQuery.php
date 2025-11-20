<?php

namespace App\GraphQL\Queries\BackOffice\Reports;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\Reports\ReportType;
use App\Models\Departments\Department;
use App\Repositories\Reports\ReportRepository;
use App\Traits\Reports\ReportEmployeeGuard;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;

class ReportsQuery extends BaseQuery
{
    use ReportEmployeeGuard;

    public const NAME = 'Reports';
    public const PERMISSION = Permissions\Reports\ListPermission::KEY;

    public function __construct(protected ReportRepository $repo)
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool
    {
        $this->setAuthGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            [
                'id' => Type::id(),
                'department_id' => [
                    'type' => Type::id(),
                    'rules' => ['nullable', 'int', Rule::exists( Department::class, 'id')],
                ],
                'date_from' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string', DatetimeEnum::DATE_TIME_RULE],
                    'description' => 'Filter by field "call_at" FROM given date, format - "Y-m-d H:i:s", timezone - UTC',
                ],
                'date_to' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string', DatetimeEnum::DATE_TIME_RULE],
                    'description' => 'Filter by field "call_at" TO given date, format - "Y-m-d H:i:s", timezone - UTC',
                ],
                'search' => Type::string()
            ],
        );
    }

    public function type(): Type
    {
        return ReportType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        $args = $this->modifyArgs($args);
        $args['has_active_department'] = true;

        return $this->repo->getPagination(
            relation: ['items'],
            filters: $args
        );
    }
}
