<?php

namespace App\GraphQL\Types\Employees;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Departments\DepartmentType;
use App\GraphQL\Types\Enums;
use App\GraphQL\Types\Sips\SipType;
use App\Models\Employees\Employee;
use GraphQL\Type\Definition\Type;

class EmployeeType extends BaseType
{
    public const NAME = 'EmployeeType';
    public const MODEL = Employee::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'status' => [
                    'type' => Enums\Employees\StatusEnum::Type(),
                ],
                'first_name' => [
                    'type' => Type::string(),
                ],
                'last_name' => [
                    'type' => Type::string(),
                ],
                'email' => [
                    'type' => Type::string(),
                ],
                'department' => [
                    'type' => DepartmentType::nonNullType(),
                ],
                'sip' => [
                    'type' => SipType::Type(),
                ],
                'email_verified_at' => [
                    'type' => Type::string(),
                ],
                'has_subscriber_record' => [
                    'type' => Type::boolean(),
                    'resolve' => static fn(Employee $m): bool => $m->hasSubscriberRecord(),
                    'description' => 'Есть ли запись, по данному пользователю, в таблице "subscriber" (kamailio)'
                ],
            ]
        );
    }
}

