<?php

namespace App\GraphQL\Types\Payments;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Payments\PaymentCard;

class MemberPaymentCardType extends BaseType
{
    public const NAME = 'memberPaymentCard';
    public const MODEL = PaymentCard::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'type' => [
                    'type' => NonNullType::string(),
                ],
                'code' => [
                    'type' => NonNullType::string(),
                ],
                'expiration_date' => [
                    'type' => NonNullType::string(),
                ],
                'default' => [
                    'type' => NonNullType::boolean(),
                ]
            ]
        );
    }
}
