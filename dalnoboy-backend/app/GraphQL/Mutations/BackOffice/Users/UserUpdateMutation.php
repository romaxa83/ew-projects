<?php

namespace App\GraphQL\Mutations\BackOffice\Users;

use App\Dto\Users\UserDto;
use App\GraphQL\InputTypes\Users\UserInputType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Permissions\Users\UserUpdatePermission;
use App\Services\Users\UserService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UserUpdateMutation extends BaseMutation
{
    public const NAME = 'userUpdate';
    public const PERMISSION = UserUpdatePermission::KEY;

    public function __construct(private UserService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(User::class, 'id')
                ]
            ],
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
            fn() => $this->service->update(
                UserDto::byArgs($args['user']),
                User::find($args['id'])
            )
        );
    }
}
