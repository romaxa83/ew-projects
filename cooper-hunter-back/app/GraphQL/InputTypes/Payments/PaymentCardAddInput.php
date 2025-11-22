<?php

namespace App\GraphQL\InputTypes\Payments;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class PaymentCardAddInput extends BaseInputType
{
    public const NAME = 'PaymentCardAddInput';

    public function fields(): array
    {
        return [
            'type' => [
                'type' => NonNullType::string(),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'number' => [
                'type' => NonNullType::string(),
            ],
            'cvc' => [
                'type' => NonNullType::string(),
            ],
            'expiration_date' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}

