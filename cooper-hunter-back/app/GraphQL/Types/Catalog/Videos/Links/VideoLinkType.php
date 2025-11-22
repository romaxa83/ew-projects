<?php

namespace App\GraphQL\Types\Catalog\Videos\Links;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Videos\Groups;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Videos\VideoLink;

class VideoLinkType extends BaseType
{
    public const NAME = 'VideoLinkType';
    public const MODEL = VideoLink::class;

    public function fields(): array
    {
        $fields = [
            'id' => ['type' => NonNullType::id(),],
            'active' => ['type' => NonNullType::boolean()],
            'link_type' => ['type' => VideoLinkTypeEnumType::nonNullType()],
            'link' => ['type' => NonNullType::string()],
            'group' => [
                'type' => Groups\VideoGroupType::type(),
                'is_relation' => true,
            ],
            'translation' => [
                'type' => TranslateType::nonNullType(),
                'is_relation' => true,
            ],
            'translations' => [
                'type' => TranslateType::nonNullList(),
                'is_relation' => true,
            ],
        ];

        return array_merge(
            parent::fields(),
            $fields
        );
    }
}

