<?php

namespace App\GraphQL\InputTypes\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Protocol;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Rules\PhoneRule;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class CommercialProjectInput extends BaseInputType
{
    public const NAME = 'CommercialProjectInput';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => NonNullType::string(),
            ],
            'address_line_1' => [
                'type' => NonNullType::string(),
            ],
            'address_line_2' => [
                'type' => Type::string(),
            ],
            'country_code' => [
                'type' => NonNullType::string(),
                ['required', 'int', Rule::exists(Country::class, 'country_code')]
            ],
            'state_id' => [
                'type' => NonNullType::id(),
                ['required', 'int', Rule::exists(State::class, 'id')]
            ],
            'city' => [
                'type' => NonNullType::string(),
            ],
            'zip' => [
                'type' => NonNullType::string(),
            ],
            'first_name' => [
                'type' => NonNullType::string(),
            ],
            'last_name' => [
                'type' => NonNullType::string(),
            ],
            'phone' => [
                'type' => NonNullType::string(),
                'rules' => [new PhoneRule()],
            ],
            'email' => [
                'type' => NonNullType::string(),
                'rules' => ['email:filter'],
            ],
            'company_name' => [
                'type' => NonNullType::string(),
            ],
            'company_address' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
            'estimate_start_date' => [
                'type' => NonNullType::string(),
                'rules' => ['string', DatetimeEnum::DATE_RULE],
                'description' => 'Date in format Y-m-d H:i:s',
            ],
            'estimate_end_date' => [
                'type' => NonNullType::string(),
                'rules' => ['string', DatetimeEnum::DATE_RULE],
                'description' => 'Date in format Y-m-d H:i:s',
            ],
        ];
    }
}
