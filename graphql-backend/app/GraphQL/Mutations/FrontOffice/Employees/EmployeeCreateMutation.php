<?php

namespace App\GraphQL\Mutations\FrontOffice\Employees;

use App\Dto\Users\UserDto;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Notifications\Users\SendPasswordNotification;
use App\Permissions\Employees\EmployeeCreatePermission;
use App\Rules\NameRule;
use App\Services\Users\EmployeeService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class EmployeeCreateMutation extends BaseMutation
{
    public const NAME = 'employeeCreate';
    public const PERMISSION = EmployeeCreatePermission::KEY;

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
            'first_name' => Type::nonNull(Type::string()),
            'last_name' => Type::nonNull(Type::string()),
            'middle_name' => Type::nonNull(Type::string()),
            'email' => Type::nonNull(Type::string()),
            'password' => Type::nonNull(Type::string()),
            'role_id' => NonNullType::id(),
            'send_email' => [
                'type' => Type::boolean(),
                'defaultValue' => true
            ],
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): User
    {
        $userDto = UserDto::byArgs($args)->setLang($this->company()->lang);

        $sendMail = data_get($args, 'send_email', true);

        $employee = make_transaction(
            fn() => $this->employeeService->create(
                $this->manager(),
                $userDto,
            )
        );

        if ($sendMail) {
            Notification::route('mail', $employee->getEmailString())
                ->notify(
                    (new SendPasswordNotification($employee, $args['password']))
                        ->locale(app()->getLocale())
                );
        }

        return $employee;
    }

    protected function rules(array $args = []): array
    {
        return $this->guest()
            ? []
            : [
                'first_name' => ['required', 'string', new NameRule('first_name')],
                'last_name' => ['required', 'string', new NameRule('last_name')],
                'middle_name' => ['required', 'string', new NameRule('middle_name')],
                'email' => ['required', 'string', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
                'role_id' => [
                    'required',
                    'int',
                    Rule::exists(config('permission.table_names.roles', 'roles'), 'id')
                        ->where(
                            function ($query) {
                                return $query->where('guard_name', User::GUARD);
                            }
                        )
                ],
                'send_email' => ['boolean'],
            ];
    }
}
