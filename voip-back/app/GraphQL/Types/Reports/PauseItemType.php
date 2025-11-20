<?php

namespace App\GraphQL\Types\Reports;

use App\GraphQL\Types\BaseType;
use App\Models\Reports;
use GraphQL\Type\Definition\Type;

class PauseItemType extends BaseType
{
    public const NAME = 'PauseItemType';
    public const MODEL = Reports\PauseItem::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'pause_at' => [
                    'type' => Type::string(),
                ],
                'unpause_at' => [
                    'type' => Type::string(),
                ],
                'duration' => [
                    'type' => Type::int(),
                    'resolve' => static fn(Reports\PauseItem $m): int => $m->getDiffAtBySec(),
                ],
            ]
        );
    }
}

