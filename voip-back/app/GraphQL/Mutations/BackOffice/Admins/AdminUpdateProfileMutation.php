<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Dto\Admins\AdminProfileDto;
use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Rules\PasswordRule;
use App\Services\Admins\AdminService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class AdminUpdateProfileMutation extends BaseMutation
{
    public const NAME = 'AdminProfileUpdate';

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
            'email' => Type::string(),
            'password' => Type::string(),
            'notify' => Type::boolean(),
        ];
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Admin
    {
        return $this->adminService->updateProfile(
            $this->user(),
            AdminProfileDto::byArgs($args)
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'name' => ['nullable', 'string'],
            'password' => ['nullable', 'string', new PasswordRule()],
            'email' => ['nullable', 'string', 'email',
                Rule::unique(Admin::TABLE)
                    ->ignore($this->authId())
            ],
        ];
    }
}

