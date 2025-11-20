<?php

namespace App\GraphQL\Queries\BackOffice\Reports;

use App\Entities\Messages\ResponseMessageEntity;
use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\Enums\Reports\ReportStatusEnum;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Reports\Report;
use App\Services\Reports\ReportItemService;
use App\Traits\FilterArgsTrait;
use App\Traits\Reports\ReportEmployeeGuard;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;
use Closure;

class ReportItemsExcelQuery extends BaseQuery
{
    use ReportEmployeeGuard;
    use FilterArgsTrait;

    public const NAME = 'ReportItemsExcel';
    public const PERMISSION = Permissions\Reports\DownloadPermission::KEY;

    public function __construct(
        protected ReportItemService $service
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
            'report_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists( Report::class, 'id')],
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
            'search' => [
                'type' => Type::string(),
            ],
            'status' => [
                'type' => ReportStatusEnum::type(),
            ],
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
        $this->itemsGuard($args);
        $args = self::removeNullableArgs($args);

        try {
            return ResponseMessageEntity::success(
                $this->service->generateExcel($args)
            );
        } catch (\Throwable $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }
}

