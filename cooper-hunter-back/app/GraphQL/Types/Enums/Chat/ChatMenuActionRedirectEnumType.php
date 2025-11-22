<?php


namespace App\GraphQL\Types\Enums\Chat;


use App\Enums\Chat\ChatMenuActionRedirectEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class ChatMenuActionRedirectEnumType extends GenericBaseEnumType
{
    public const NAME = 'ChatMenuActionRedirectEnumType';
    public const ENUM_CLASS = ChatMenuActionRedirectEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
