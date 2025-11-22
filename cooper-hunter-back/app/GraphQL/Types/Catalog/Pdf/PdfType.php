<?php

namespace App\GraphQL\Types\Catalog\Pdf;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Pdf\Pdf;

class PdfType extends BaseType
{
    public const NAME = 'PdfType';
    public const MODEL = Pdf::class;

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
                'resolve' => static fn(Pdf $m) => $m->getFirstMedia(Pdf::MEDIA_COLLECTION_NAME)
            ],
        ];
    }
}

