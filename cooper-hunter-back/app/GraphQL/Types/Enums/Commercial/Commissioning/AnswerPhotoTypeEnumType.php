<?php

namespace App\GraphQL\Types\Enums\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerPhotoType;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class AnswerPhotoTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'CommissioningAnswerPhotoTypeEnumType';
    public const ENUM_CLASS = AnswerPhotoType::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
