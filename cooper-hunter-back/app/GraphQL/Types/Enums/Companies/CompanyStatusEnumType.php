<?php

namespace App\GraphQL\Types\Enums\Companies;

use App\Enums\Companies\CompanyStatus;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class CompanyStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'CompanyStatusEnumType';
    public const ENUM_CLASS = CompanyStatus::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
