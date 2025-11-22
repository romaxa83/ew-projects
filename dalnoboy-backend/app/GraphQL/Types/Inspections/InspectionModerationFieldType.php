<?php


namespace App\GraphQL\Types\Inspections;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Inspections\InspectionModerationEntityEnumType;
use App\GraphQL\Types\Enums\Inspections\InspectionModerationFieldEnumType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class InspectionModerationFieldType extends BaseType
{
    public const NAME = 'InspectionModerationFieldType';

    public function fields(): array
    {
        return [
            'field' => [
                'type' => InspectionModerationFieldEnumType::nonNullType(),
            ],
            'entity' => [
                'type' => InspectionModerationEntityEnumType::nonNullType(),
            ],
            'id' => [
                'type' => Type::int(),
            ],
            'message' => [
                'type' => NonNullType::string(),
                'resolve' => fn(array $field) => trans($field['message'])
            ]
        ];
    }
}
