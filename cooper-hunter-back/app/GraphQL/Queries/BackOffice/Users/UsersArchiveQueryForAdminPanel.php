<?php

namespace App\GraphQL\Queries\BackOffice\Users;

use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Permissions\Users\UserArchiveListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class UsersArchiveQueryForAdminPanel extends BaseQuery
{
    public const NAME = 'usersArchiveForAdminPanel';
    public const PERMISSION = UserArchiveListPermission::KEY;

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
        return $this->paginateType(
            UserType::type()
        );
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            User::query()
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
