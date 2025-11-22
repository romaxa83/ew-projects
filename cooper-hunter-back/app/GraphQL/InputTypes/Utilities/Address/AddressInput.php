<?php

namespace App\GraphQL\InputTypes\Utilities\Address;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class AddressInput extends BaseInputType
{
    public const NAME = 'AddressInput';

    public function fields(): array
    {
        return [
            'country_code' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string', Rule::exists(Country::class, 'country_code')]
            ],
            'state_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(State::class, 'id')]
            ],
            'city' => [
                'type' => NonNullType::string(),
            ],
            'address_line_1' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string']
            ],
            'address_line_2' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string']
            ],
            'zip' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}


