<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Services\Admins\AdminService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AdminRecoverPasswordMutation extends BaseMutation
{
    public const NAME = 'adminRecoverPassword';

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
            'email' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'email',
                    Rule::exists(Admin::class, 'email'),
                ],
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
            fn() => $this->service->recoverPassword(
                $args['email']
            )
        );
    }
}
