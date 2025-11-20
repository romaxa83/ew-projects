<?php

namespace App\GraphQL\Types\Reports;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Employees\EmployeeType;
use App\Models\Reports\Report;
use GraphQL\Type\Definition\Type;

class ReportType extends BaseType
{
    public const NAME = 'ReportType';
    public const MODEL = Report::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'employee' => [
                    'alias' => 'employeeWithTrashed',
                    'type' => EmployeeType::Type(),
                ],
                'items' => [
                    'is_relation' => true,
                    'type' => ItemType::list(),
                ],
                'calls' => [
                    'type' => Type::int(),
                    'resolve' => static fn(Report $m): int => $m->getCallsCount(),
                ],
                'answered_calls' => [
                    'type' => Type::int(),
                    'resolve' => static fn(Report $m): int => $m->getAnsweredCallsCount(),
                ],
                'dropped_calls' => [
                    'type' => Type::int(),
                    'resolve' => static fn(Report $m): int => $m->getDroppedCallsCount(),
                ],
                'transfer_calls' => [
                    'type' => Type::int(),
                    'resolve' => static fn(Report $m): int => $m->getTransferCallsCount(),
                ],
                'wait' => [
                    'type' => Type::int(),
                    'resolve' => static fn(Report $m): int => $m->getTotalWait(),
                ],
                'total_time' => [
                    'type' => Type::int(),
                    'resolve' => static fn(Report $m): int => $m->getTotalTime(),
                ],
                'pause' => [
                    'type' => Type::int(),
                    'resolve' => static fn(Report $m): int => $m->getPauseCount(),
                ],
                'total_pause_time' => [
                    'type' => Type::int(),
                    'resolve' => static fn(Report $m): int => $m->getTotalPauseTime(),
                ],
                'pause_items' => [
                    'is_relation' => true,
                    'alias' => 'pauseItems',
                    'type' => PauseItemType::list(),
                ],
            ]
        );
    }
}
