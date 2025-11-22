<?php

namespace App\GraphQL\Types\About;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\About\ForMemberPageEnumType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\About\ForMemberPage;

class ForMemberPageType extends BaseType
{
    public const NAME = 'ForMemberPageType';
    public const MODEL = ForMemberPage::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'for_member_type' => [
                'type' => ForMemberPageEnumType::nonNullType(),
            ],
            'image' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => fn(ForMemberPage $f) => $f->getFirstMedia($f::MEDIA_COLLECTION_NAME),
            ],
            'translation' => [
                'type' => ForMemberPageTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => ForMemberPageTranslationType::nonNullList(),
            ],
        ];
    }
}
