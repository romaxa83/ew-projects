<?php

namespace App\GraphQL\Types\Media;

use GraphQL\Type\Definition\Type;

class MediaConversionType extends \Core\GraphQL\Types\MediaConversionType
{
    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'url_webp' => [
                    'type' => Type::string(),
                ],
            ]
        );
    }
}
