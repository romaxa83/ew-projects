<?php

namespace App\GraphQL\Types\Calls\History;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Departments\DepartmentType;
use App\GraphQL\Types\Employees\EmployeeType;
use App\GraphQL\Types\Enums\Calls\HistoryStatusEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\Calls\History;
use GraphQL\Type\Definition\Type;

class HistoryType extends BaseType
{
    public const NAME = 'CallHistoryType';
    public const MODEL = History::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'status' => [
                    'type' => HistoryStatusEnum::Type(),
                ],
                'department' => [
                    'type' => DepartmentType::Type(),
                ],
                'employee' => [
                    'type' => EmployeeType::Type(),
                ],
                'from_num' => [
                    'type' => Type::string(),
                    'description' => 'Номер инициатора звонка',
                    'resolve' => static fn(History $m): ?string => $m->getFromNumber(),
                ],
                'from_name' => [
                    'type' => Type::string(),
                    'description' => 'Имя инициатора звонка',
                    'resolve' => static fn(History $m): ?string => $m->getFromName(),
                ],
                'dialed' => [
                    'type' => Type::string(),
                    'description' => 'Номер принявшего звонок',
                    'resolve' => static fn(History $m): ?string => $m->getDialed(),
                ],
                'dialed_name' => [
                    'type' => Type::string(),
                    'resolve' => static fn(History $m): ?string => $m->getDialedName(),
                ],
                'duration' => [
                    'type' => Type::int(),
                    'description' => 'Время звонка, вместе с ожиданием (сек.)'
                ],
                'billsec' => [
                    'type' => Type::int(),
                    'description' => 'Время звонка, без ожиданием (сек.)'
                ],
                'serial_numbers' => [
                    'type' => Type::string(),
                ],
                'case_id' => [
                    'type' => Type::string(),
                ],
                'comment' => [
                    'type' => Type::string(),
                ],
                'call_date' => [
                    'type' => NonNullType::string(),
                ],
                'call_record_link' => [
                    'type' => Type::string(),
                    'resolve' => static fn(History $m): ?string => $m->getUrlAudioRecord(),
                    'description' => 'Ссылка на аудио запись звонка, формируется только если история имеет статус "answered", "transfer"'
                ],
            ]
        );
    }
}

