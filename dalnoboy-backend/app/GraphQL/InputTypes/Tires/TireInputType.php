<?php

namespace App\GraphQL\InputTypes\Tires;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Dictionaries\TireSpecification;
use App\Rules\Tires\TireMutationRule;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class TireInputType extends BaseInputType
{
    public const NAME = 'TireInputType';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
                'description' => 'For FrontOffice only true - field does not depend on value',
            ],
            'serial_number' => [
                'type' => NonNullType::string(),
            ],
            'specification_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'bail',
                    Rule::exists(TireSpecification::class, 'id')
                ],
            ],
            'relationship_type_id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    'int',
                    Rule::exists(TireRelationshipType::class, 'id')
                ],
            ],
            'is_moderated' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
                'description' => 'For FrontOffice only false - field does not depend on value',
            ],
            'is_offline' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => false,
                'description' => 'For FrontOffice only',
            ],
            'change_reason_id' => [
                'type' => Type::id(),
                'description' => 'Required in FrontOffice for update',
                'rules' => [
                    'nullable',
                    new TireMutationRule()
                ]
            ],
            'change_reason_description' => [
                'type' => Type::string(),
            ],
            'ogp' => [
                'type' => Type::float(),
                'description' => 'For Update Mutation only',
            ]
        ];
    }
}
