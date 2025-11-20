<?php

namespace App\GraphQL\Mutations\BackOffice\Auth\Employee;

use App\GraphQL\Types\Auth\LoginTokenType;
use App\Models\Employees\Employee;
use App\Services\Auth\EmployeePassportService;
use Core\GraphQL\Mutations\BaseTokenRefreshMutation;
use Core\Services\Auth\AuthPassportService;
use GraphQL\Type\Definition\Type;

class EmployeeTokenRefreshMutation extends BaseTokenRefreshMutation
{
    public const NAME = 'EmployeeRefreshToken';

    public function __construct(
        protected EmployeePassportService $passportService
    )
    {}

    protected function getPassportService(): AuthPassportService
    {
        return $this->passportService;
    }

    protected function getGuard(): string
    {
        return Employee::GUARD;
    }

    public function type(): Type
    {
        return LoginTokenType::type();
    }
}
