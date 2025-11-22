<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireType;
use Illuminate\Validation\Rule;

class TireSpecificationInputType extends BaseInputType
{
    public const NAME = 'TireSpecificationInputType';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
                'description' => 'For FrontOffice only true - field does not depend on value',
            ],
            'make_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'bail',
                    Rule::exists(TireMake::class, 'id')
                ],
            ],
            'model_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'bail',
                    Rule::exists(TireModel::class, 'id')
                ],
            ],
            'type_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'bail',
                    Rule::exists(TireType::class, 'id')
                ],
            ],
            'size_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'bail',
                    Rule::exists(TireSize::class, 'id'),
                ],
            ],
            'ngp' => [
                'type' => NonNullType::float(),
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
        ];
    }
}
