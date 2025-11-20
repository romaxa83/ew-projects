<?php

namespace App\GraphQL\Types\Calls\Queue;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Departments\DepartmentType;
use App\GraphQL\Types\Employees\EmployeeType;
use App\GraphQL\Types\Enums\Calls\QueueStatusEnum;
use App\GraphQL\Types\Enums\Calls\QueueTypeEnum;
use App\Models\Calls\Queue;
use App\Traits\TimezoneTrait;
use GraphQL\Type\Definition\Type;

class QueueType extends BaseType
{
    use TimezoneTrait;

    public const NAME = 'CallQueueType';
    public const MODEL = Queue::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'department' => [
                    'type' => DepartmentType::Type(),
                ],
                'employee' => [
                    'type' => EmployeeType::Type(),
                ],
                'status' => [
                    'type' => QueueStatusEnum::Type(),
                ],
                'from' => [
                    'alias' => 'caller_num',
                    'type' => Type::string(),
                    'resolve' => static fn(Queue $m): ?string => $m->getCallerNum(),
                    'description' => 'Номер инициатора звонка'
                ],
                'from_name' => [
                    'alias' => 'caller_name',
                    'type' => Type::string(),
                    'resolve' => static fn(Queue $m): ?string => $m->getCallerName(),
                    'description' => 'Имя инициатора звонка'
                ],
                'connected' => [
                    'alias' => 'connected_num',
                    'type' => Type::string(),
                    'resolve' => static fn(Queue $m): ?string => $m->getConnectedNum(),
                    'description' => 'Номер принимающего звонок'
                ],
                'connected_name' => [
                    'alias' => 'connected_name',
                    'type' => Type::string(),
                    'resolve' => static fn(Queue $m): ?string => $m->getConnectedName(),
                    'description' => 'Имя принимающего звонок'
                ],
                'connected_at' => [
                    'type' => Type::string(),
                    'description' => 'Время когда произошел конект с агентом, но разговор еще не начался',
//                    'resolve' => static fn(Queue $m) => $m->connected_at?->setTimezone(TimezoneTrait::getTimezone())
                    'resolve' => static fn(Queue $m) => $m->connected_at
                ],
                'called_at' => [
                    'type' => Type::string(),
                    'description' => 'Время начала разговора с агентом',
//                    'resolve' => static fn(Queue $m) => $m->called_at?->setTimezone(TimezoneTrait::getTimezone())
                    'resolve' => static fn(Queue $m) => $m->called_at
                ],
                'position' => [
                    'type' => Type::int(),
                ],
                'wait' => [
                    'type' => Type::int(),
                ],
                'serial_number' => [
                    'type' => Type::string(),
                ],
                'case_id' => [
                    'type' => Type::string(),
                ],
                'comment' => [
                    'type' => Type::string(),
                ],
                'type' => [
                    'type' => QueueTypeEnum::Type(),
                ],
            ]
        );
    }
}
