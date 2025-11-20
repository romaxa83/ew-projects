<?php


namespace App\GraphQL\Types\Unions;


use App\GraphQL\Types\Admins\AdminType;
use App\GraphQL\Types\BaseUnionType;
use App\GraphQL\Types\Employees\EmployeeType;
use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Models\Users\User;
use Exception;
use GraphQL\Type\Definition\Type;

class Authenticatable extends BaseUnionType
{
    public const NAME = 'Authenticatable';

    public function types(): array
    {
        return [
            AdminType::type(),
            EmployeeType::type()
        ];
    }

    /**
     * @throws Exception
     */
    public function resolveType(Employee|Admin $value): Type
    {
        if ($value instanceof User) {
            return EmployeeType::type();
        }

        if ($value instanceof Admin) {
            return AdminType::type();
        }

        throw new Exception(__('exceptions.type_not_found'));
    }
}
