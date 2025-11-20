<?php

namespace App\GraphQL\Types\Enums\Calls;

use App\Enums;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class HistoryStatusEnum extends GenericBaseEnumType
{
    public const NAME = 'CallHistoryStatusEnumType';
    public const ENUM_CLASS = Enums\Calls\HistoryStatus::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}

