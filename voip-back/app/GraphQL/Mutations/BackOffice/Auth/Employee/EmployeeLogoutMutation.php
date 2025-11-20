<?php

namespace App\GraphQL\Mutations\BackOffice\Auth\Employee;

use App\Services\Auth\EmployeePassportService;
use Core\GraphQL\Mutations\BaseLogoutMutation;
use Core\Services\Auth\AuthPassportService;

class EmployeeLogoutMutation extends BaseLogoutMutation
{
    public const NAME = 'EmployeeLogout';

    public function __construct(
        protected EmployeePassportService $passportService
    ) {
        $this->setEmployeeGuard();
    }

    protected function getPassportService(): AuthPassportService
    {
        return $this->passportService;
    }
}
