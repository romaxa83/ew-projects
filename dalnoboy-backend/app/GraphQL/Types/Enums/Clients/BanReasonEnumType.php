<?php


namespace App\GraphQL\Types\Enums\Clients;


use App\Enums\Clients\BanReasonsEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class BanReasonEnumType extends GenericBaseEnumType
{
    public const NAME = 'BanReasonEnumType';
    public const ENUM_CLASS = BanReasonsEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
