<?php

namespace App\GraphQL\InputTypes\Orders;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\Country;

use App\Models\Locations\State;

use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Rules\PhoneRule;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class OrderShippingInput extends BaseInputType
{
    public const NAME = 'OrderShippingInput';

    public function fields(): array
    {
        return [
            'first_name' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'last_name' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'phone' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                    new PhoneRule()
                ],
            ],
            'address_first_line' => [
                'type' => NonNullType::string(),
            ],
            'address_second_line' => [
                'type' => Type::string(),
            ],
            'city' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'country_code' => [
                'type' => NonNullType::string(),
                'defaultValue' => 'US',
                'rules' => [
                    'required',
                    'string',
                    Rule::exists(Country::class, 'country_code')
                ],
//                ['required', 'int', Rule::exists(Country::class, 'country_code')]
            ],
            'state_id' => [
                'type' => NonNullType::id(),
                ['required', 'int', Rule::exists(State::class, 'id')]
            ],
            'zip' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'delivery_type' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(OrderDeliveryType::class, 'id')
                        ->where('active', true)
                ],
            ]
        ];
    }
}
