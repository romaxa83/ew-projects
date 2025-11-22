<?php

namespace App\GraphQL\InputTypes\Utilities\Upload;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use App\GraphQL\Types\FileType;

class MultiLangFileType extends BaseInputType
{
    public const NAME = 'MultiLangFileType';

    public function fields(): array
    {
        return [
            'language' => [
                'type' => LanguageTypeEnum::nonNullType(),
                'defaultValue' => 'en',
            ],
            'file' => [
                'type' => FileType::nonNullType(),
            ]
        ];
    }
}
