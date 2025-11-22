<?php

namespace App\GraphQL\Types\Enums\Orders\Dealer;

use App\Enums\Orders\Dealer\DeliveryType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class DeliveryTypeTypeEnum extends GenericBaseEnumType
{
    public const NAME = 'DealerDeliveryTypeEnumType';
    public const ENUM_CLASS = DeliveryType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
