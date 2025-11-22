<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Dto\Admins\AdminDto;
use App\GraphQL\InputTypes\Admins\AdminInputType;
use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminUpdatePermission;
use App\Services\Admins\AdminService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AdminUpdateMutation extends BaseMutation
{
    public const NAME = 'adminUpdate';
    public const PERMISSION = AdminUpdatePermission::KEY;

    public function __construct(private AdminService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return AdminType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Admin::class, 'id')
                ]
            ],
            'admin' => [
                'type' => AdminInputType::nonNullType()
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
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Admin
    {
        return makeTransaction(
            fn() => $this->service->update(
                AdminDto::byArgs($args['admin']),
                Admin::find($args['id'])
            )
        );
    }
}
