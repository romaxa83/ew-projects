<?php

namespace App\GraphQL\Types\Enums\Payments\PaymentCard;

use App\Enums\Payments\PaymentCard\MorphSupportedType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class MorphSupportedTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'PaymentCardMorphTypeEnumType';
    public const ENUM_CLASS = MorphSupportedType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
