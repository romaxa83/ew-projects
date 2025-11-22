<?php

namespace App\GraphQL\InputTypes\Orders\Dealer;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Orders\Dealer\PaymentTypeTypeEnum;
use App\Rules\TranslationsArrayValidator;

class PaymentDescInput extends BaseInputType
{
    public const NAME = 'DealerOrderPaymentDescInput';

    public function fields(): array
    {
        return [
            'type' => [
                'type' => PaymentTypeTypeEnum::Type(),
            ],
            'translations' => [
                'type' => PaymentDescTranslationInput::nonNullList(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator()
                ]
            ],
        ];
    }
}
