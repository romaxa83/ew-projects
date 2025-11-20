<?php

namespace App\GraphQL\Mutations\BackOffice\Employees;

use App\Dto\Employees\EmployeeDto;
use App\Events\Employees\EmployeeCreatedEvent;
use App\GraphQL\InputTypes\Employees\EmployeeInput;
use App\GraphQL\Types\Employees\EmployeeType;
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

class EmployeeCreateMutation extends BaseMutation
{
    public const NAME = 'EmployeeCreate';
    public const PERMISSION = Permissions\Employees\CreatePermission::KEY;

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
        $dto = EmployeeDto::byArgs($args['input']);

        $model = makeTransaction(
            fn(): Employee=> $this->service->create($dto)
        );

        event(new EmployeeCreatedEvent($model, $dto));

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.first_name' => ['required', 'string'],
            'input.last_name' => ['required', 'string'],
            'input.email' => ['required', 'string', 'email:filter', new AuthUniqueEmailRule()],
            'input.password' => ['required', 'string', new PasswordRule()],
            'input.department_id' => ['required', Rule::exists(Department::class, 'id')],
            'input.sip_id' => [
                'required',
                Rule::exists(Sip::class, 'id'),
                new SipAttachedRule()
            ]
        ];
    }
}
