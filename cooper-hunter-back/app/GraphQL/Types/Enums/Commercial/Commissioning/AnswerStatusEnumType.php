<?php

namespace App\GraphQL\Types\Enums\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class AnswerStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'CommissioningAnswerStatusEnumType';
    public const ENUM_CLASS = AnswerStatus::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}

