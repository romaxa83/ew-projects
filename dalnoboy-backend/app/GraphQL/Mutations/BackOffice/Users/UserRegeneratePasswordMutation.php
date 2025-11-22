<?php

namespace App\GraphQL\Mutations\BackOffice\Users;

use App\GraphQL\Types\NonNullType;
use App\Models\Users\User;
use App\Permissions\Users\UserUpdatePermission;
use App\Services\Users\UserService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UserRegeneratePasswordMutation extends BaseMutation
{
    public const NAME = 'userRegeneratePassword';
    public const PERMISSION = UserUpdatePermission::KEY;
    public const DESCRIPTION = 'Generate new user password and send it to mail. ';

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
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->setPassword(User::find($args['id']), true)
        );
    }
}
