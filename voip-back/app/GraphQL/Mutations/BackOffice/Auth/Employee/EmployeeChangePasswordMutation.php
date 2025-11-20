<?php

namespace App\GraphQL\Mutations\BackOffice\Auth\Employee;

use App\Services\Employees\EmployeeService;
use Core\GraphQL\Mutations\BaseChangePasswordMutation;

class EmployeeChangePasswordMutation extends BaseChangePasswordMutation
{
    public const NAME = 'EmployeeChangePassword';

    public function __construct(protected EmployeeService $service)
    {
        $this->setEmployeeGuard();
    }
}
