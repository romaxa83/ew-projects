<?php

namespace App\GraphQL\Types\Enums\Faq\Questions;

use App\Enums\Faq\Questions\QuestionStatusEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class QuestionStatusEnumType extends GenericBaseEnumType
{
    public const NAME = 'QuestionStatusEnumType';
    public const ENUM_CLASS = QuestionStatusEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
