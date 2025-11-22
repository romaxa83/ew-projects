<?php

namespace App\GraphQL\Types\Enums\Orders\Dealer;

use App\Enums\Orders\Dealer\PaymentType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class PaymentTypeTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'DealerPaymentTypeEnumType';
    public const ENUM_CLASS = PaymentType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
