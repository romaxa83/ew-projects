<?php

namespace App\GraphQL\Types\Enums\Schedules;

use App\Enums;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class DayEnumType extends GenericBaseEnumType
{
    public const NAME = 'DayEnumType';
    public const ENUM_CLASS = Enums\Formats\DayEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}



