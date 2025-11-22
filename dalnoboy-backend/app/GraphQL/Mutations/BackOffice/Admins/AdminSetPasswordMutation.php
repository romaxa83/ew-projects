<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Rules\PasswordRule;
use App\Services\Admins\AdminService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AdminSetPasswordMutation extends BaseMutation
{
    public const NAME = 'adminSetPassword';

    public function __construct(private AdminService $service)
    {
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    public function args(): array
    {
        return [
            'token' => [
                'type' => NonNullType::string(),
            ],
            'password' => [
                'type' => NonNullType::string(),
                'rules' => [
                    new PasswordRule(),
                    'confirmed',
                ],
            ],
            'password_confirmation' => [
                'type' => NonNullType::string(),
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Admin
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->setNewPassword(
                $args['token'],
                $args['password']
            )
        );
    }
}
