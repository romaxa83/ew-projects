<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireDiameter;
use App\Models\Dictionaries\TireHeight;
use App\Models\Dictionaries\TireWidth;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class TireSizeInputType extends BaseInputType
{
    public const NAME = 'TireSizeInputType';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
                'description' => 'For FrontOffice only true - field does not depend on value',
            ],
            'tire_width_id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    'bail',
                    Rule::exists(TireWidth::class, 'id'),
                ],
            ],
            'tire_height_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'bail',
                    Rule::exists(TireHeight::class, 'id'),
                ],
            ],
            'tire_diameter_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'bail',
                    Rule::exists(TireDiameter::class, 'id'),
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
        ];
    }
}
