<?php

namespace App\GraphQL\Mutations\BackOffice\Users;

use App\Dto\Users\UserDto;
use App\GraphQL\InputTypes\Users\UserInputType;
use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Permissions\Users\UserCreatePermission;
use App\Services\Users\UserService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UserCreateMutation extends BaseMutation
{
    public const NAME = 'userCreate';
    public const PERMISSION = UserCreatePermission::KEY;

    public function __construct(private UserService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'user' => [
                'type' => UserInputType::nonNullType()
            ]
        ];
    }

    public function type(): Type
    {
        return UserType::nonNullType();
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return User
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): User
    {
        return makeTransaction(
            fn() => $this->service->create(
                UserDto::byArgs($args['user'])
            )
        );
    }
}
