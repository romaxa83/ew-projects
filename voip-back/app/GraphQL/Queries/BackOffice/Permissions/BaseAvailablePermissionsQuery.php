<?php

namespace App\GraphQL\Queries\BackOffice\Permissions;

use App\GraphQL\Types\Roles\GrantGroupType;
use App\Traits\PermissionsHelper;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use Core\Services\Permissions\PermissionService;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseAvailablePermissionsQuery extends BaseQuery
{
    use PermissionsHelper;

    public function __construct(protected PermissionService $permissionService)
    {
        $this->setAdminGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return Type::listOf(
            GrantGroupType::type()
        );
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): array
    {
        $perms = $this->permissionService
            ->getGroupsFor($this->getPermissionGuard())
            ->toArray();

        return PermissionsHelper::deleteHiddenPerms(
            $perms, $this->getPermissionGuard()
        );
    }

    abstract protected function getPermissionGuard(): string;
}
