<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Dto\Admins\AdminDto;
use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminUpdatePermission;
use App\Rules\NameRule;
use App\Services\Admins\AdminService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class AdminUpdateMutation extends BaseMutation
{
    public const NAME = 'adminUpdate';
    public const PERMISSION = AdminUpdatePermission::KEY;

    public function __construct(private AdminService $adminService)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return AdminType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
            'admin' => AdminDto::nonNullType(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Admin
    {
        return $this->adminService->update(
            Admin::query()->findOrFail($args['id']),
            AdminDto::byArgs($args['admin'])
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:admins,id'],
            'admin.name' => ['required', 'string', new NameRule()],
            'admin.email' => [
                'required',
                'string',
                'email',
                Rule::unique(Admin::TABLE, 'email')
                    ->ignore($args['id'])
            ],
            'admin.password' => ['nullable', 'string', 'min:8'],
            'admin.role_id' => [
                'required',
                'int',
                Rule::exists(config('permission.table_names.roles', 'roles'), 'id')
                    ->where(function ($query) {
                        return $query->where('guard_name', $this->guard);
                    })
            ]
        ];
    }
}
