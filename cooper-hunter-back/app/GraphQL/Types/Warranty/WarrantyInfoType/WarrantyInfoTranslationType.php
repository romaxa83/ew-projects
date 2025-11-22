<?php

namespace App\GraphQL\Types\Warranty\WarrantyInfoType;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoTranslation;
use GraphQL\Type\Definition\Type;

class WarrantyInfoTranslationType extends BaseTranslationType
{
    public const NAME = 'WarrantyInfoTranslationType';
    public const MODEL = WarrantyInfoTranslation::class;

    public function fields(): array
    {
        return parent::fields() + [
                'description' => [
                    'type' => NonNullType::string(),
                ],
                'notice' => [
                    'type' => NonNullType::string(),
                ],
                'seo_title' => [
                    'type' => Type::string(),
                ],
                'seo_description' => [
                    'type' => Type::string(),
                ],
                'seo_h1' => [
                    'type' => Type::string(),
                ],
            ];
    }
}
