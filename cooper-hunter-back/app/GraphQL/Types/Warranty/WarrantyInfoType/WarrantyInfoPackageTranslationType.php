<?php

namespace App\GraphQL\Types\Warranty\WarrantyInfoType;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackageTranslation;

class WarrantyInfoPackageTranslationType extends BaseTranslationType
{
    public const NAME = 'WarrantyInfoPackageTranslationType';
    public const MODEL = WarrantyInfoPackageTranslation::class;

    public function fields(): array
    {
        return parent::fields() + [
                'title' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
                    'type' => NonNullType::string(),
                ],
            ];
    }
}
