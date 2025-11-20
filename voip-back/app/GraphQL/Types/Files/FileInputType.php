<?php

namespace App\GraphQL\Types\Files;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\UploadType;

class FileInputType extends BaseInputType
{
    public const NAME = 'FileInput';

    public function fields(): array
    {
        return [
            'file' => [
                'type' => UploadType::nonNullType(),
                'description' => 'Файл для загрузки.'
            ],
        ];
    }
}
