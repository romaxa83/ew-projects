<?php

namespace App\GraphQL\Types\Enums\Commercial;

use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class CommercialQuoteStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'CommercialQuoteStatusEnumType';
    public const ENUM_CLASS = CommercialQuoteStatusEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
