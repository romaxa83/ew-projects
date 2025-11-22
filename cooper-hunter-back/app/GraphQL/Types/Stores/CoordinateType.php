<?php

namespace App\GraphQL\Types\Stores;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\ValueObjects\Point;

class CoordinateType extends BaseType
{
    public const NAME = 'CoordinateType';

    public function fields(): array
    {
        return [
            'longitude' => [
                'type' => NonNullType::float(),
                'resolve' => static fn(Point $p) => $p->getLongitude(),
            ],
            'latitude' => [
                'type' => NonNullType::float(),
                'resolve' => static fn(Point $p) => $p->getLatitude(),
            ],
        ];
    }
}
