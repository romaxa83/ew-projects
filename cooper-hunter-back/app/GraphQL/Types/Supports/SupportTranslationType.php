<?php

namespace App\GraphQL\Types\Supports;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Support\Supports\SupportTranslation;

class SupportTranslationType extends BaseTranslationType
{
    public const NAME = 'SupportTranslationType';
    public const MODEL = SupportTranslation::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'description' => [
                    'type' => NonNullType::string(),
                ],
                'short_description' => [
                    'type' => NonNullType::string(),
                ],
                'working_time' => [
                    'type' => NonNullType::string(),
                ],
                'video_link' => [
                    'type' => NonNullType::string(),
                ],
            ]
        );
    }
}
