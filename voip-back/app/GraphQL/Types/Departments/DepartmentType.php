<?php

namespace App\GraphQL\Types\Departments;

use App\GraphQL\Types\BaseType;
use App\Models\Departments\Department;
use GraphQL\Type\Definition\Type;

class DepartmentType extends BaseType
{
    public const NAME = 'DepartmentType';
    public const MODEL = Department::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'sort' => [
                    'type' => Type::int(),
                ],
                'active' => [
                    'type' => Type::boolean(),
                ],
                'name' => [
                    'type' => Type::string(),
                ],
                'has_queue_record' => [
                    'type' => Type::boolean(),
                    'resolve' => static fn(Department $m): bool => $m->hasQueueRecord(),
                    'description' => 'Есть ли запись, по данному департаменту, в таблице "queues" (asterisk)'
                ],
                'employees_count' => [
//                    'is_relation' => false,
                    'resolve' => static fn(Department $m): int => $m->employeesCount(),
                    'type' => Type::int(),
                ],
            ]
        );
    }
}
