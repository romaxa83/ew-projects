<?php

namespace App\GraphQL\Types\Enums\Companies;

use App\Enums\Companies\CompanyType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class CompanyTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'CompanyTypeEnumType';
    public const ENUM_CLASS = CompanyType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
