<?php

namespace App\GraphQL\Types\Enums\About;

use App\Enums\About\ForMemberPageEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class ForMemberPageEnumType extends GenericBaseEnumType
{
    public const NAME = 'ForMemberPageEnumType';
    public const ENUM_CLASS = ForMemberPageEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
