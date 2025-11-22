<?php


namespace App\GraphQL\Types\Vehicles\Schemas;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Vehicles\Schemas\SchemaAxle;

class SchemaAxleType extends BaseType
{
    public const NAME = 'SchemaAxleType';
    public const MODEL = SchemaAxle::class;

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
                'wheels' => [
                    'type' => SchemaWheelType::nonNullList(),
                    'is_relation' => true
                ]
            ]
        );
    }

}
