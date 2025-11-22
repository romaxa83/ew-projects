<?php

namespace App\GraphQL\Types\Catalog\Manuals;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Manuals\Manual;

class ManualType extends BaseType
{
    public const NAME = 'ManualType';
    public const MODEL = Manual::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'pdf' => [
                'type' => MediaType::nonNullType(),
                'alias' => 'media',
                'always' => 'id',
                'resolve' => static fn(Manual $m) => $m->getFirstMedia(Manual::MEDIA_COLLECTION_NAME)
            ],
            'group' => [
                'type' => ManualGroupType::nonNullType(),
            ],
        ];
    }
}
