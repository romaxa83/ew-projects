<?php

namespace App\GraphQL\Types\Warranty\WarrantyInfoType;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackage;

class WarrantyInfoPackageType extends BaseType
{
    public const NAME = 'WarrantyInfoPackageType';
    public const MODEL = WarrantyInfoPackage::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'sort' => [
                'type' => NonNullType::int(),
            ],
            'image' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => static fn(WarrantyInfoPackage $m) => $m->getFirstMedia(
                    WarrantyInfoPackage::MEDIA_COLLECTION_NAME
                )
            ],
            'translation' => [
                'type' => WarrantyInfoPackageTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => WarrantyInfoPackageTranslationType::nonNullList(),
            ],
        ];
    }
}
