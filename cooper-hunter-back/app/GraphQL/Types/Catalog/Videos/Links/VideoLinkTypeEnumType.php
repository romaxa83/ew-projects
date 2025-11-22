<?php

namespace App\GraphQL\Types\Catalog\Videos\Links;

use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\GraphQL\Types\GenericBaseEnumType;
use Core\GraphQL\Types\Enums\GraphQLTypeCastedEnum;
use GraphQL\Type\Definition\Type as GraphqlType;

class VideoLinkTypeEnumType extends GenericBaseEnumType
{
    public const NAME = 'VideoLinkTypeEnumType';
    public const ENUM_CLASS = VideoLinkTypeEnum::class;

    public function toType(): GraphqlType
    {
        return new GraphQLTypeCastedEnum($this->toArray());
    }
}
