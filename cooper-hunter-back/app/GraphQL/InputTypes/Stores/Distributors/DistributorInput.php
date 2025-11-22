<?php

namespace App\GraphQL\InputTypes\Stores\Distributors;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\State;
use App\Rules\PhoneRule;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class DistributorInput extends BaseInputType
{
    public const NAME = 'DistributorInput';

    public function fields(): array
    {
        return [
            'state_id' => [
                'type' => Type::id(),
                'rules' => ['nullable', 'int', Rule::exists(State::class, 'id')],
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'coordinates' => [
                'type' => CoordinateInput::nonNullType(),
            ],
            'address' => [
                'type' => NonNullType::string(),
            ],
            'link' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'url'],
            ],
            'phone' => [
                'type' => Type::string(),
                'rules' => ['nullable', new PhoneRule()]
            ],
            'translations' => [
                'type' => DistributorTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
