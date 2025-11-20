<?php

namespace App\GraphQL\Queries\BackOffice\Calls\History;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\Calls\History\HistoryType;
use App\GraphQL\Types\Enums\Calls\HistoryStatusEnum;
use App\Models\Employees\Employee;
use App\Permissions;
use App\Repositories\Calls\HistoryRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class HistoriesQuery extends BaseQuery
{
    public const NAME = 'CallHistories';
    public const PERMISSION = Permissions\Calls\History\ListPermission::KEY;

    public function __construct(protected HistoryRepository $repo)
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
                'department_id' => Type::id(),
                'employee_id' => Type::id(),
                'status' => HistoryStatusEnum::type(),
                'serial_number' => [
                    'type' => Type::string(),
                    'description' => 'Поиск по серийному номеру',
                ],
                'case_id' => [
                    'type' => Type::string(),
                    'description' => 'Поиск по case id',
                ],
                'search' => [
                    'type' => Type::string(),
                    'description' => 'Поиск по имени или телефону клиента и агента(сотрудника)',
                ],
                'date_from' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string', DatetimeEnum::DATE_TIME_RULE],
                    'description' => 'Filter by field "call_date" FROM given date, format - "Y-m-d H:i:s", timezone - UTC',
                ],
                'date_to' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string', DatetimeEnum::DATE_TIME_RULE],
                    'description' => 'Filter by field "call_date" TO given date, format - "Y-m-d H:i:s", timezone - UTC',
                ],
                'sort' => [
                    'type' => Type::string(),
                    'rules' => ['nullable', 'string'],
                    'description' => 'Сортировка по полю, передается в таком формате - call_date-asc или call_date-desc',
                ],
            ],
        );
    }

    public function type(): Type
    {
        return HistoryType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        if($this->user() instanceof Employee){
            $args['employee_id'] = $this->user()->id;
        }

        return $this->repo->getPagination(
            filters: $args
        );
    }
}

