<?php

namespace App\GraphQL\Types\Sliders;

use App\GraphQL\Types\BaseTranslationType;
use App\Models\Sliders\SliderTranslation;
use GraphQL\Type\Definition\Type;

class SliderTranslationType extends BaseTranslationType
{
    public const NAME = 'SliderTranslationType';
    public const MODEL = SliderTranslation::class;

    public function fields(): array
    {
        return parent::fields() + [
                'title' => [
                    'type' => Type::string(),
                ],
                'description' => [
                    'type' => Type::string(),
                ],
            ];
    }
}
