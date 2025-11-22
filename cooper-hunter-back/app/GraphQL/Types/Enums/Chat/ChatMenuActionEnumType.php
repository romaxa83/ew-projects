<?php


namespace App\GraphQL\Types\Enums\Chat;


use App\Enums\Chat\ChatMenuActionEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class ChatMenuActionEnumType extends GenericBaseEnumType
{
    public const NAME = 'ChatMenuActionEnumType';
    public const ENUM_CLASS = ChatMenuActionEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
