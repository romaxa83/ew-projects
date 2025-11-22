<?php

namespace App\GraphQL\Queries\FrontOffice\Users;

use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Permissions\Users\UserShowPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class UserQuery extends BaseQuery
{
    public const NAME = 'user';
    public const PERMISSION = UserShowPermission::KEY;

    public function __construct()
    {
        $this->setUserGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return UserType::nonNullType();
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): User
    {
        return $this->user()
            ->load($fields->getRelations());
    }
}
