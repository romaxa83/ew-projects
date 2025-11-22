<?php

namespace App\GraphQL\Types\Orders\Dealer\PaymentDesc;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Orders\Dealer\PaymentTypeTypeEnum;
use App\Models\About\Page;

class PaymentDescType extends BaseType
{
    public const NAME = 'dealerOrderPaymentDescType';
    public const MODEL = Page::class;

    public function fields(): array
    {
        return [
            'type' => [
                'type' => PaymentTypeTypeEnum::nonNullType(),
                'alias' => 'slug',
            ],
            'translation' => [
                'type' => PaymentDescTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => PaymentDescTranslationType::nonNullList(),
            ],
        ];
    }
}
