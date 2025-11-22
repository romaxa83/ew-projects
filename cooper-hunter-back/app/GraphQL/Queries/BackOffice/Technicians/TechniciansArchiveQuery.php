<?php

namespace App\GraphQL\Queries\BackOffice\Technicians;

use App\GraphQL\Types\Technicians\TechnicianType;
use App\Models\Technicians\Technician;
use App\Permissions\Technicians\TechnicianArchiveListPermission;
use App\Permissions\Technicians\TechnicianListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class TechniciansArchiveQuery extends BaseQuery
{
    public const NAME = 'techniciansArchive';
    public const PERMISSION = TechnicianArchiveListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            [
                'query' => Type::string(),
            ]
        );
    }

    public function type(): Type
    {
        return TechnicianType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            Technician::query()
                ->onlyTrashed()
                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations())
                ->latest(),
            $args
        );
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            $this->paginationRules(),
            [
                'query' => ['nullable', 'string'],
            ]
        );
    }
}

