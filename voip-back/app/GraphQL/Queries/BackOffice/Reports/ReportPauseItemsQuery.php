<?php

namespace App\GraphQL\Queries\BackOffice\Reports;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Reports\PauseItemType;
use App\Models\Reports\Report;
use App\Repositories\Reports\ReportPauseItemRepository;
use App\Traits\Reports\ReportEmployeeGuard;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;

class ReportPauseItemsQuery extends BaseQuery
{
    use ReportEmployeeGuard;

    public const NAME = 'ReportPauseItems';
    public const PERMISSION = Permissions\Reports\ListPermission::KEY;

    public function __construct(protected ReportPauseItemRepository $repo)
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
                    'description' => 'Filter by field "pause_at" FROM given date, format - "Y-m-d H:i:s", timezone - UTC',
                ],
                'date_to' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string', DatetimeEnum::DATE_TIME_RULE],
                    'description' => 'Filter by field "unpause_at" TO given date, format - "Y-m-d H:i:s", timezone - UTC',
                ],
            ],
        );
    }

    public function type(): Type
    {
        return PauseItemType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        $this->itemsGuard($args);

        return $this->repo->getPagination(
            filters: $args
        );
    }
}
