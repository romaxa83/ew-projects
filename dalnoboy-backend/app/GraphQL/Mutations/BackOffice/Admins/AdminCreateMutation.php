<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Dto\Admins\AdminDto;
use App\GraphQL\InputTypes\Admins\AdminInputType;
use App\GraphQL\Types\Admins\AdminType;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminCreatePermission;
use App\Services\Admins\AdminService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AdminCreateMutation extends BaseMutation
{
    public const NAME = 'adminCreate';
    public const PERMISSION = AdminCreatePermission::KEY;

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
            fn() => $this->service->create(
                AdminDto::byArgs($args['admin'])
            )
        );
    }
}
