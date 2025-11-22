<?php

namespace App\GraphQL\Types\Enums\Orders;

use App\Enums\Orders\OrderCostStatusEnum;
use App\GraphQL\Types\BaseEnumType;

class OrderFilterCostStatusTypeEnum extends BaseEnumType
{

    public const NAME = 'OrderFilterCostStatusTypeEnum';
    public const DESCRIPTION = 'Available order cost statuses for filter';
    public const ENUM_CLASS = OrderCostStatusEnum::class;

    public function attributes(): array
    {
        /** @var OrderCostStatusEnum $class */
        $class = static::ENUM_CLASS;

        return array_merge(
            parent::attributes(),
            [
                'values' => collect($class::getFilterValues())
                    ->mapWithKeys(static fn(string $type) => [$type => $type])
                    ->toArray()
            ]
        );
    }
}
