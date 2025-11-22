<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\Exceptions\Permissions\RoleForOwnerException;
use App\GraphQL\Types\NonNullType;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleDeletePermission;
use App\Rules\RoleIdValidator;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Services\Permissions\PermissionService;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseRoleDeleteMutation extends BaseMutation
{
    public function __construct(protected PermissionService $permissionService)
    {
        $this->setAdminGuard();
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->can(RoleDeletePermission::KEY);
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $role = Role::query()
            ->where('guard_name', static::roleGuard())
            ->findOrFail($args['id']);

        try {
            return make_transaction(
                fn() => $this->permissionService->deleteRole($role)
            );
        } catch (RoleForOwnerException $e) {
            throw $e;
        } catch (Throwable) {
            return false;
        }
    }

    abstract protected function roleGuard(): string;

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', app(RoleIdValidator::class, ['guard' => static::roleGuard()])],
        ];
    }
}
