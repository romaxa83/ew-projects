<?php

namespace App\GraphQL\Queries\BackOffice\Reports;

use App\Entities\Messages\ResponseMessageEntity;
use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Services\Reports\ReportService;
use App\Traits\FilterArgsTrait;
use App\Traits\Reports\ReportEmployeeGuard;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;
use Closure;

class ReportsExcelQuery extends BaseQuery
{
    use ReportEmployeeGuard;
    use FilterArgsTrait;

    public const NAME = 'ReportsExcel';
    public const PERMISSION = Permissions\Reports\DownloadPermission::KEY;

    public function __construct(
        protected ReportService $service
    )
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
        return [
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
        ];
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity
    {
        $args = $this->modifyArgs($args);
        $args = self::removeNullableArgs($args);
        $args['has_active_department'] = true;

        try {
            return ResponseMessageEntity::success(
                $this->service->generateReportExcel($args)
            );
        } catch (\Throwable $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}
