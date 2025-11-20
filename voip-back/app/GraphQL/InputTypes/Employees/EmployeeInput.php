<?php

namespace App\GraphQL\InputTypes\Employees;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class EmployeeInput extends BaseInputType
{
    public const NAME = 'EmployeeInputType';

    public function fields(): array
    {
        return [
            'first_name' => [
                'type' => NonNullType::string(),
            ],
            'last_name' => [
                'type' => NonNullType::string(),
            ],
            'email' => [
                'type' => NonNullType::string(),
            ],
            'password' => [
                'type' => Type::string(),
            ],
            'department_id' => [
                'type' => NonNullType::id(),
            ],
            'sip_id' => [
                'type' => Type::id(),
            ],
            'send_email' => [
                'type' => Type::boolean(),
                'description' => "Отправлять ли email c кредами (по дефолту false)"
            ],
        ];
    }
}

