<?php

namespace App\GraphQL\Mutations\BackOffice\Auth;

use App\GraphQL\Types\Auth\LoginTokenType;
use App\Models\Admins\Admin;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Repositories\Admins\AdminRepository;
use App\Repositories\Employees\EmployeeRepository;
use App\Rules\LoginAdmin;
use App\Rules\LoginEmployee;
use App\Rules\PasswordRule;
use App\Services\Auth\AdminPassportService;
use App\Services\Auth\EmployeePassportService;
use Core\GraphQL\Mutations\BaseLoginMutation;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class LoginMutation extends BaseLoginMutation
{
    public const NAME = 'Login';

    public function __construct(
        protected AdminPassportService $adminPassportService,
        protected EmployeePassportService $employeePassportService,
        protected AdminRepository $adminRepo,
        protected EmployeeRepository $employeeRepo,
    ) {}

    public function type(): Type
    {
        return LoginTokenType::type();
    }

    protected function rules(array $args = []): array
    {
        if ($this->employeeRepo->existBy(['email' => $args['username']])) {
            $this->setEmployeeGuard();
            $rule = new LoginEmployee($args);
        } else {
            $this->setAdminGuard();
            $rule = new LoginAdmin($args);
        }
        $rules = parent::rules($args);

        $rules['username'] = ['required', 'email:filter',
            Rule::exists($this->guard === Employee::GUARD ? Employee::class : Admin::class , 'email')
        ];
        $rules['password'] = ['required', 'string', new PasswordRule(), $rule];

        return $rules;
    }

    protected function getPassportService()
    : AdminPassportService|EmployeePassportService
    {
        return match ($this->guard) {
            Admin::GUARD => $this->adminPassportService,
            Employee::GUARD => $this->employeePassportService,
        };
    }
}
