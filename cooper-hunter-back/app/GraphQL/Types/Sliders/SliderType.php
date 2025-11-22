<?php

namespace App\GraphQL\Types\Sliders;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Sliders\Slider;
use GraphQL\Type\Definition\Type;

class SliderType extends BaseType
{
    public const NAME = 'Slider';
    public const MODEL = Slider::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'link' => [
                'type' => Type::string(),
            ],
            'media' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => fn(Slider $s) => $s->getFirstMedia($s::MEDIA_COLLECTION_NAME),
            ],
            'translation' => [
                'type' => SliderTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => SliderTranslationType::nonNullList(),
            ],
        ];
    }
}
