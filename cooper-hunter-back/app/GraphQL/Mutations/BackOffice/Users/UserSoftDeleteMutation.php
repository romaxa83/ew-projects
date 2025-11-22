<?php

namespace App\GraphQL\Mutations\BackOffice\Users;

use App\GraphQL\Types\NonNullType;
use App\Models\Users\User;
use App\Permissions\Users\UserSoftDeletePermission;
use App\Services\Users\UserService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UserSoftDeleteMutation extends BaseMutation
{
    public const NAME = 'usersSoftDelete';
    public const PERMISSION = UserSoftDeletePermission::KEY;

    public function __construct(private UserService $userService)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'ids' => Type::nonNull(Type::listOf(NonNullType::id())),
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $users = User::query()
            ->whereKey($args['ids'])
            ->get();

        return makeTransaction(
            fn() => $this->userService->softDeleted($users)
        );
    }

    protected function rules(array $args = []): array
    {
        if ($this->guest() || !$this->isAdmin()) {
            return [];
        }

        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'int', Rule::exists(User::TABLE, 'id')]
        ];
    }
}

