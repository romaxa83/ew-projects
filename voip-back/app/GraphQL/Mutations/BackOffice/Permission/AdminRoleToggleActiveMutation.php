<?php

namespace App\GraphQL\Mutations\BackOffice\Permission;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleUpdatePermission;
use App\Repositories\Admins\AdminRepository;
use App\Services\Admins\AdminService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Services\Permissions\RoleService;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AdminRoleToggleActiveMutation extends BaseMutation
{
    public const NAME = 'AdminRoleToggleActive';
    public const PERMISSION = RoleUpdatePermission::KEY;

    public function __construct(
        protected RoleService $service,
        protected AdminRepository $adminRepo,
        protected AdminService $adminService,
    )
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool
    {
        $this->setAdminGuard();

        return Auth::user() && Auth::user()->isSuperAdmin();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Role::class, 'id')],
            ],
        ];
    }

    public function type(): Type
    {
        return RoleType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Role
    {
        /** @var Role $role */
        $role = $this->service->toggleActive($args['id']);

        $this->adminRepo->getByRole($role)->map(
            fn(Admin $admin) => $role->isActive()
                ? $this->adminService->activate($admin)
                : $this->adminService->deactivate($admin)
        );

        return $role;
    }
}
