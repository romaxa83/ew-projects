<?php

namespace App\GraphQL\Types\Enums\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class QuestionStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'CommissioningQuestionStatusEnumType';
    public const ENUM_CLASS = QuestionStatus::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}

