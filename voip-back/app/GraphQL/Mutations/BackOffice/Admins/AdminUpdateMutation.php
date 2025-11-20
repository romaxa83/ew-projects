<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Dto\Admins\AdminDto;
use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminUpdatePermission;
use App\Rules\NameRule;
use App\Services\Admins\AdminService;
use App\Traits\Permissions\AccessHelper;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class AdminUpdateMutation extends BaseMutation
{
    use AccessHelper;

    public const NAME = 'AdminUpdate';
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
            'name' => Type::string(),
            'email' => NonNullType::string(),
            'password' => Type::string(),
            'role_id' => NonNullType::id(),
            'relations' => [
                'type' => Type::listOf(NonNullType::id()),
                'description' => 'Привязанные админы, пустой массив удалит все связи',
            ],
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Admin
    {
        $this->checkOwnerAccount($args['id']);

        return $this->adminService->update(
            Admin::query()->findOrFail($args['id']),
            AdminDto::byArgs($args)
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:admins,id'],
            'name' => ['required', 'string', new NameRule()],
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique(Admin::TABLE)
                    ->ignore($args['id'])
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => [
                'required',
                'int',
                Rule::exists(config('permission.table_names.roles', 'roles'), 'id')
                    ->where(function ($query) {
                        return $query->where('guard_name', $this->guard);
                    })
            ],
            'relations' => ['nullable', 'array', Rule::exists(Admin::class, 'id')],
        ];
    }
}
