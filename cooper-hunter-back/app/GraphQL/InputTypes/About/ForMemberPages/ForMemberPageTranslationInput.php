<?php

namespace App\GraphQL\InputTypes\About\ForMemberPages;

use App\GraphQL\InputTypes\BaseTranslationInput;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class ForMemberPageTranslationInput extends BaseTranslationInput
{
    public const NAME = 'ForMemberPageTranslationInput';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'title' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
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
            ]
        );
    }
}
