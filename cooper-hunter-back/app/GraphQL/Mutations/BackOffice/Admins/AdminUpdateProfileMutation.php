<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Dto\Admins\AdminDto;
use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminUpdatePermission;
use App\Services\Admins\AdminService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class AdminUpdateProfileMutation extends BaseMutation
{
    public const NAME = 'adminProfileUpdate';
    public const PERMISSION = AdminUpdatePermission::KEY;

    public function __construct(protected AdminService $adminService)
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
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Admin
    {
        return $this->adminService->update(
            $this->user(),
            AdminDto::byArgs($args)
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique(Admin::TABLE)
                    ->ignore($this->authId())
            ],
        ];
    }
}
