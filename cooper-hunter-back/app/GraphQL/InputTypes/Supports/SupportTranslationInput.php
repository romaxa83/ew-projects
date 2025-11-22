<?php

namespace App\GraphQL\InputTypes\Supports;

use App\GraphQL\InputTypes\BaseTranslationInput;
use App\GraphQL\Types\NonNullType;

class SupportTranslationInput extends BaseTranslationInput
{
    public const NAME = 'SupportTranslationInput';

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
                    'description' => 'eg.: Monday through Friday, 9am to 5 pm',
                ],
                'video_link' => [
                    'type' => NonNullType::string(),
                    'rules' => ['url'],
                ],
            ],
        );
    }
}
