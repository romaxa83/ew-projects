<?php

namespace App\GraphQL\Mutations\BackOffice\Employees;

use App\Dto\Employees\EmployeeDto;
use App\GraphQL\InputTypes\Employees\EmployeeInput;
use App\GraphQL\Types\Employees\EmployeeType;
use App\GraphQL\Types\NonNullType;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use App\Permissions;
use App\Rules\Auth\AuthUniqueEmailRule;
use App\Rules\PasswordRule;
use App\Rules\Sip\SipAttachedRule;
use App\Services\Employees\EmployeeService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class EmployeeUpdateMutation extends BaseMutation
{
    public const NAME = 'EmployeeUpdate';
    public const PERMISSION = Permissions\Employees\ChangeStatusPermission::KEY;

    public function __construct(
        protected EmployeeService $service
    )
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null)
    : bool
    {
        $this->setAdminGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Employee::class, 'id')],
            ],
            'input' => EmployeeInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return EmployeeType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Employee
    {
        /** @var $model Employee */
        $model = $this->service->repo->getBy('id', $args['id']);

        return $this->service->update(
            $model,
            EmployeeDto::byArgs($args['input'])
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.first_name' => ['required', 'string'],
            'input.last_name' => ['required', 'string'],
            'input.email' => ['required', 'string', 'email:filter', AuthUniqueEmailRule::ignoreEmployee($args['id'])],
            'input.password' => ['nullable', 'string', new PasswordRule()],
            'input.department_id' => ['required', 'string', Rule::exists(Department::class, 'id')],
            'input.sip_id' => [
                'nullable',
                Rule::exists(Sip::class, 'id'),
                new SipAttachedRule(data_get($args, 'id'))
            ]
        ];
    }
}
