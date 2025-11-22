<?php

namespace App\GraphQL\InputTypes\Orders\Dealer;

use App\GraphQL\Types\BaseInputTranslateType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use App\GraphQL\Types\NonNullType;

class PaymentDescTranslationInput extends BaseInputTranslateType
{
    public const NAME = 'DealerOrderPaymentDescTranslationInput';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => LanguageTypeEnum::nonNullType(),
            ],
            'description' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
