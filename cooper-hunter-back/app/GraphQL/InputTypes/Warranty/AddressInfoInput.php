<?php

namespace App\GraphQL\InputTypes\Warranty;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use Closure;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AddressInfoInput extends BaseInputType
{
    public const NAME = 'WarrantyAddressInfoInput';

    public function fields(): array
    {
        return [
            'country_code' => [
                'type' => NonNullType::string(),
                'defaultValue' => 'US',
                'rules' => [
                    'required',
                    'string',
                    Rule::exists(Country::class, 'country_code')
                ]
            ],
            'state_id' => [
//                'type' => NonNullType::id(),
//                'rules' => ['required', 'int', Rule::exists(State::class, 'id')]
                'type' => Type::id(),
                'rules' => [
                    'required',
//                    'required_without:state',
                    'int',
                    Rule::exists(State::class, 'id')
                ]

            ],
//            'state' => [
//                'type' => Type::string(),
//                'rules' => [
//                    'nullable',
//                    'required_without:state_id',
//                    'string',
//                    static function (string $attribute, string $value, Closure $fail) {
//                        $slug = Str::slug($value);
//                        if (State::where('slug', $slug)
//                            ->exists()) {
//                            return;
//                        }
//                        $fail('Incorrect state field');
//                    }
//                ]
//            ],
            'street' => [
                'type' => NonNullType::string(),
            ],
            'city' => [
                'type' => NonNullType::string(),
            ],
            'zip' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
