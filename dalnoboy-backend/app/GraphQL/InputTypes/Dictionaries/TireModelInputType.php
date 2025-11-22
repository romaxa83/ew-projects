<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireMake;
use Illuminate\Validation\Rule;

class TireModelInputType extends BaseInputType
{
    public const NAME = 'TireModelInputType';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
                'description' => 'For FrontOffice only true - field does not depend on value',
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'tire_make_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'bail',
                    Rule::exists(TireMake::class, 'id')
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
