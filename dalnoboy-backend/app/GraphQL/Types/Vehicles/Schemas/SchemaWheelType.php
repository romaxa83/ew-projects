<?php

namespace App\GraphQL\Types\Vehicles\Schemas;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Vehicles\Schemas\SchemaWheel;

class SchemaWheelType extends BaseType
{
    public const NAME = 'SchemaWheelType';
    public const MODEL = SchemaWheel::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'position' => [
                    'type' => NonNullType::int(),
                ],
                'name' => [
                    'type' => NonNullType::string(),
                ],
                'use' => [
                    'type' => NonNullType::boolean(),
                    'description' => 'Flag for wheels which using in thi schema'
                ],
                'required' => [
                    'type' => NonNullType::boolean(),
                    'selectable' => false,
                    'always' => ['id', 'schema_axle_id'],
                    'resolve' => static fn(SchemaWheel $m): int => $m->required(),
                    'description' => 'Обязательно к заполнению'
                ]
            ]
        );
    }
}
