<?php

namespace Core\Traits\GraphQL\Inputs;

use App\GraphQL\Types\NonNullType;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

trait AddressInputTrait
{
    public function addressFields(): array
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
                'rules' => ['required', 'string']
            ],
            'address_line_1' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string']
            ],
            'address_line_2' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string']
            ],
            'zip' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string']
            ],
            'po_box' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string']
            ],
        ];
    }
}
