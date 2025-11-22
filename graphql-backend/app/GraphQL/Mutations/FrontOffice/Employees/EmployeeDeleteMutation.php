<?php

namespace App\GraphQL\Mutations\FrontOffice\Employees;

use App\GraphQL\Types\NonNullType;
use App\Models\Users\User;
use App\Permissions\Employees\EmployeeDeletePermission;
use App\Rules\ExistsRules\UserInCompanyExistsRule;
use App\Services\Users\EmployeeService;
use Core\GraphQL\Mutations\BaseMutation;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class EmployeeDeleteMutation extends BaseMutation
{
    public const NAME = 'employeeDelete';
    public const PERMISSION = EmployeeDeletePermission::KEY;

    public function __construct(private EmployeeService $employeeService)
    {
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'ids' => NonNullType::listOf(NonNullType::id()),
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $employees = User::query()
            ->whereSameCompany($this->manager())
            ->whereKey($args['ids'])
            ->get();

        make_transaction(
            function () use ($employees) {
                $employees->each(function (User $user) {
                    $this->checkBeforeDeleting($user);
                });

                $this->employeeService->delete($employees);
            }
        );

        return true;
    }

    /**
     * @throws Exception
     */
    protected function checkBeforeDeleting(User $employee): void
    {
        if ($employee->isOwner()) {
            throw new Exception(__('exceptions.employee.owner_cant_be_deleted'));
        }

        if ($employee->getKey() === $this->user()->getKey()) {
            throw new Exception(__('exceptions.employee.cannot_delete_yourself'));
        }
    }

    protected function rules(array $args = []): array
    {
        return $this->guest()
            ? []
            : [
                'ids' => ['required', 'array'],
                'ids.*' => ['required', 'integer', new UserInCompanyExistsRule($this->company())],
            ];
    }
}
