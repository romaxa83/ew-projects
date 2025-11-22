<?php

namespace App\GraphQL\Types\Orders\Dealer\PaymentDesc;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\About\PageTranslation;

class PaymentDescTranslationType extends BaseType
{
    public const NAME = 'dealerOrderPaymentDescTranslationType';
    public const MODEL = PageTranslation::class;

    public function fields(): array
    {
        return [
            'description' => [
                'type' => NonNullType::string(),
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
