<?php

namespace App\GraphQL\Queries\BackOffice\Reports;

use App\Entities\Reports\ReportItemAdditionalEntity;
use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\Enums\Reports\ReportStatusEnum;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Reports\ReportItemAdditionalType;
use App\Models\Reports\Report;
use App\Repositories\Reports\ReportItemRepository;
use App\Traits\Reports\ReportEmployeeGuard;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;

class ReportItemsAdditionalQuery extends BaseQuery
{
    use ReportEmployeeGuard;

    public const NAME = 'ReportItemsAdditional';
    public const PERMISSION = Permissions\Reports\ListPermission::KEY;

    public function __construct(protected ReportItemRepository $repo)
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
            ],
        );
    }

    public function type(): Type
    {
        return ReportItemAdditionalType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ReportItemAdditionalEntity
    {
        $this->itemsGuard($args);

        return $this->repo->getAdditionalData(
            filters: $args
        );
    }
}


