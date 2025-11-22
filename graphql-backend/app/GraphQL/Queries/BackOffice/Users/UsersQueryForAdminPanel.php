<?php

namespace App\GraphQL\Queries\BackOffice\Users;

use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Permissions\Users\UserListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class UsersQueryForAdminPanel extends BaseQuery
{
    public const NAME = 'usersForAdminPanel';
    public const PERMISSION = UserListPermission::KEY;

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

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): LengthAwarePaginator
    {
        return $this->paginate(
            User::query()
                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations()),
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
