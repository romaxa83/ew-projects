<?php

namespace App\GraphQL\Queries\BackOffice\Projects;

use App\GraphQL\Types\Enums\Users\MemberMorphTypeEnum;
use App\GraphQL\Types\Projects\ProjectType;
use App\Models\Projects\Project;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Permissions\Projects\ProjectListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class ProjectsQuery extends BaseQuery
{
    public const NAME = 'memberProjects';
    public const PERMISSION = ProjectListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'id' => [
                    'type' => Type::id()
                ],
                'user_id' => [
                    'type' => Type::id(),
                ],
                'technician_id' => [
                    'type' => Type::id(),
                ],
                'name' => [
                    'type' => Type::string(),
                    'description' => 'Filter by project name.'
                ],
                'member_id' => [
                    'type' => Type::id(),
                    'description' => 'Filter by member id.',
                ],
                'member_type' => [
                    'type' => MemberMorphTypeEnum::type(),
                    'description' => 'Filter by creator type.',
                ],
                'member_name' => [
                    'type' => Type::string(),
                    'description' => 'Filter by creator name.',
                ],
                'member_email' => [
                    'type' => Type::string(),
                    'description' => 'Filter by creator email.',
                ],
                'serial_number' => [
                    'type' => Type::string(),
                    'description' => 'Filter by serial number.',
                ]
            ],
            $this->paginationArgs(),
        );
    }

    public function type(): Type
    {
        return ProjectType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            Project::query()
                ->whereHasMorph('member', [User::class, Technician::class])
                ->filter($args)
                ->select($fields->getSelect() ?: ['id'])
                ->latest(),
            $args,
        );
    }
}
