<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Dto\Permission\RoleDto;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\RoleTranslateInputType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleCreatePermission;
use App\Rules\RoleUniqueNameValidator;
use App\Rules\TranslationsArrayValidator;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Rules\PermissionKeyValidator;
use Core\Services\Permissions\PermissionService;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseRoleCreateMutation extends BaseMutation
{
    public const PERMISSION = RoleCreatePermission::KEY;

    public function __construct(private PermissionService $permissionService)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'name' => NonNullType::string(),
            'translations' => NonNullType::listOf(RoleTranslateInputType::type()),
            'permissions' => Type::listOf(Type::string()),
        ];
    }

    public function type(): Type
    {
        return RoleType::type();
    }

    /**
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Role
    {
        return makeTransaction(
            fn() => $this->permissionService->createRole(
                RoleDto::makeByArgs($args),
                static::roleGuard()
            )
        );
    }

    abstract protected function roleGuard(): string;

    protected function rules(array $args = []): array
    {
        return [
            'name' => ['required', 'string', 'min:3', new RoleUniqueNameValidator(null, Admin::GUARD)],
            'translations' => [new TranslationsArrayValidator()],
            'translations.*.title' => ['required', 'string', 'min:3'],
            'permissions' => ['array'],
            'permissions.*' => [
                'string',
                'min:4',
                new PermissionKeyValidator(static::roleGuard())
            ],
        ];
    }
}
