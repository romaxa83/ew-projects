<?php


namespace App\GraphQL\Types\Enums\Users;


use App\Enums\Users\AuthorizationExpirationPeriodEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class AuthorizationExpirationPeriodEnumType extends GenericBaseEnumType
{
    public const NAME = 'AuthorizationExpirationPeriodEnumType';
    public const ENUM_CLASS = AuthorizationExpirationPeriodEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
