<?php

namespace App\GraphQL\Mutations\BackOffice\Users;

use App\GraphQL\Types\NonNullType;
use App\Models\Users\User;
use App\Permissions\Users\UserDeletePermission;
use App\Services\Users\EmployeeService;
use Core\GraphQL\Mutations\BaseMutation;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UserDeleteMutation extends BaseMutation
{
    public const NAME = 'usersDelete';
    public const PERMISSION = UserDeletePermission::KEY;

    public function __construct(private EmployeeService $employeeService)
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

        $this->checkUsersBeforeDelete($users);

        return make_transaction(
            fn() => $this->employeeService->delete($users)
        );
    }

    /**
     * @throws Exception
     */
    private function checkUsersBeforeDelete(Collection $users): void
    {
        foreach ($users as $user) {
            if ($user->isOwner()) {
                throw new Exception(__('exceptions.employee.owner_cant_be_deleted'));
            }
        }
    }

    protected function rules(array $args = []): array
    {
        if ($this->guest() || !$this->isAdmin()) {
            return [];
        }

        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:users,id']
        ];
    }
}
