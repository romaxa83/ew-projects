<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Dto\Permission\RoleDto;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\RoleTranslateInputType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleUpdatePermission;
use App\Rules\RoleIdValidator;
use App\Rules\RoleUniqueNameValidator;
use App\Rules\TranslatesArrayValidator;
use App\Traits\Permissions\AccessHelper;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Rules\PermissionKeyValidator;
use Core\Services\Permissions\PermissionService;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Auth;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseRoleUpdateMutation extends BaseMutation
{
    use AccessHelper;

    public const PERMISSION = RoleUpdatePermission::KEY;

    public function __construct(
        private PermissionService $permissionService
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
            'translations' => NonNullType::listOf(RoleTranslateInputType::type()),
            'permissions' => Type::listOf(Type::string()),
        ];
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->assetSuperAdmin();
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
        $role = Role::query()
            ->with('translations')
            ->findOrFail($args['id']);

        return makeTransaction(
            fn() => $this->permissionService->updateRole(
                $role,
                RoleDto::makeByArgs($args),
                static::roleGuard()
            )
        );
    }

    abstract protected function roleGuard(): string;

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', app(RoleIdValidator::class, ['guard' => static::roleGuard()])],
            'translations' => [app(TranslatesArrayValidator::class)],
            'translations.*.title' => ['required', 'string', 'min:3'],
            'permissions' => ['required', 'array'],
            'permissions.*' => [
                'string',
                'min:4',
                new PermissionKeyValidator(static::roleGuard()),
            ],
        ];
    }
}
