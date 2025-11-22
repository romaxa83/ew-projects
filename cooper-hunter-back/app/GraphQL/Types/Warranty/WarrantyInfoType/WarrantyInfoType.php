<?php

namespace App\GraphQL\Types\Warranty\WarrantyInfoType;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Warranty\WarrantyInfo\WarrantyInfo;
use GraphQL\Type\Definition\Type;

class WarrantyInfoType extends BaseType
{
    public const NAME = 'WarrantyInfoType';
    public const MODEL = WarrantyInfo::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'translation' => [
                'type' => WarrantyInfoTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => WarrantyInfoTranslationType::nonNullList(),
            ],
            'pdf' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => static fn(WarrantyInfo $m) => $m->getFirstMedia(WarrantyInfo::MEDIA_COLLECTION_NAME)
            ],
            'video_link' => [
                'type' => Type::string(),
            ],
            'packages' => [
                'type' => WarrantyInfoPackageType::list(),
            ],
        ];
    }
}
