<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Dto\Admins\AdminDto;
use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminCreatePermission;
use App\Rules\PasswordRule;
use App\Services\Admins\AdminService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class AdminCreateMutation extends BaseMutation
{
    public const NAME = 'adminCreate';
    public const PERMISSION = AdminCreatePermission::KEY;

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
            'name' => Type::string(),
            'email' => NonNullType::string(),
            'password' => NonNullType::string(),
            'role_id' => NonNullType::id()
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Admin
    {
        return $this->adminService->create(
            AdminDto::byArgs($args)
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique:admins'],
            'password' => ['required', 'string', new PasswordRule()],
            'role_id' => [
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
