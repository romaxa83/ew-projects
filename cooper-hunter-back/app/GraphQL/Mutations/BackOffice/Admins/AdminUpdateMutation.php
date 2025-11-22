<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Dto\Admins\AdminDto;
use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Services\Admins\AdminService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class AdminUpdateMutation extends BaseMutation
{
    public const NAME = 'adminUpdate';
    public const DESCRIPTION = 'изменение админов доступно супер админам';

    public function __construct(protected AdminService $adminService)
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
        return $this->authCheck() && $this->user()->isSuperAdmin();
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
            'role_id' => NonNullType::id()
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Admin
    {
        return $this->adminService->update(
            Admin::query()->findOrFail($args['id']),
            AdminDto::byArgs($args)
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:admins,id'],
            'name' => ['required', 'string'],
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
            ]
        ];
    }
}
