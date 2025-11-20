<?php

namespace App\GraphQL\Types\Reports;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Reports\ReportStatusEnum;
use App\Models\Reports;
use App\Models\Reports\Item;
use GraphQL\Type\Definition\Type;

class ItemType extends BaseType
{
    public const NAME = 'ItemType';
    public const MODEL = Reports\Item::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'status' => [
                    'type' => ReportStatusEnum::Type(),
                ],
                'number' => [
                    'type' => Type::string(),
                    'resolve' => static fn(Item $m): ?string => $m->getNum(),
                ],
                'name' => [
                    'type' => Type::string(),
                    'resolve' => static fn(Item $m): ?string => $m->getName(),
                ],
                'wait' => [
                    'type' => Type::int(),
                ],
                'total_time' => [
                    'type' => Type::int(),
                ],
                'call_at' => [
                    'type' => Type::string(),
                ],
            ]
        );
    }
}
