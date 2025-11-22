<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\VehicleMake;
use Illuminate\Validation\Rule;

class VehicleModelInputType extends BaseInputType
{
    public const NAME = 'VehicleModelInputType';

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
            'vehicle_make_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'nullable',
                    'bail',
                    Rule::exists(VehicleMake::class, 'id')
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
