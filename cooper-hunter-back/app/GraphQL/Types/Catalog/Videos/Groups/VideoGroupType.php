<?php

namespace App\GraphQL\Types\Catalog\Videos\Groups;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Videos\Links;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Videos\Group;
use GraphQL\Type\Definition\Type as GraphQLType;

class VideoGroupType extends BaseType
{
    public const NAME = 'VideoGroupType';
    public const MODEL = Group::class;

    public function fields(): array
    {
        $fields = [
            'id' => ['type' => NonNullType::id(),],
            'active' => ['type' => GraphQLType::nonNull(GraphQLType::boolean())],
            'links' => [
                'type' => GraphQLType::nonNull(GraphQLType::listOf(Links\VideoLinkType::type())),
                'is_relation' => true,
            ],
            'translation' => [
                'type' => GraphQLType::nonNull(TranslateType::type()),
                'is_relation' => true,
            ],
            'translations' => [
                'type' => GraphQLType::nonNull(GraphQLType::listOf(TranslateType::type())),
                'is_relation' => true,
            ],
        ];

        return array_merge(
            parent::fields(),
            $fields
        );
    }
}
