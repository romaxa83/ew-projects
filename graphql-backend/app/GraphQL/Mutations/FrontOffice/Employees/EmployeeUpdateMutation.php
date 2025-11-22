<?php

namespace App\GraphQL\Mutations\FrontOffice\Employees;

use App\Dto\Users\UserDto;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Notifications\Users\ChangePasswordNotification;
use App\Permissions\Employees\EmployeeUpdatePermission;
use App\Rules\NameRule;
use App\Services\Users\EmployeeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class EmployeeUpdateMutation extends BaseMutation
{
    public const NAME = 'employeeUpdate';
    public const PERMISSION = EmployeeUpdatePermission::KEY;

    public function __construct(private EmployeeService $employeeService)
    {
    }

    public function type(): Type
    {
        return UserType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
            'first_name' => Type::string(),
            'last_name' => Type::string(),
            'middle_name' => Type::string(),
            'email' => NonNullType::string(),
            'password' => Type::string(),
            'role_id' => NonNullType::id(),
            'send_email' => [
                'type' => Type::boolean(),
                'defaultValue' => true
            ]
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): User
    {
        $employee = User::query()
            ->whereSameCompany($this->manager())
            ->findOrFail($args['id']);

        $updatedEmployee = make_transaction(
            fn() => $this->employeeService->update(
                $employee,
                UserDto::byArgs($args)
            )
        );

        if ($args['send_email'] && isset($args['password'])) {
            Notification::route('mail', $employee->getEmailString())
                ->notify(
                    (new ChangePasswordNotification($employee, $args['password']))
                        ->locale(app()->getLocale())
                );
        }

        return $updatedEmployee;
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:users,id'],
            'first_name' => ['required', 'string', new NameRule('first_name')],
            'last_name' => ['required', 'string', new NameRule('last_name')],
            'middle_name' => ['required', 'string', new NameRule('middle_name')],
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique(User::TABLE)
                    ->ignore($args['id'])
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => [
                'required',
                'int',
                Rule::exists(config('permission.table_names.roles', 'roles'), 'id')
                    ->where(function ($query) {
                        return $query->where('guard_name', User::GUARD);
                    })
            ],
        ];
    }
}
