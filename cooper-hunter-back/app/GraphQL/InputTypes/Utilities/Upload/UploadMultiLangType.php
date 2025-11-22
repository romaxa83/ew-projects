<?php

namespace App\GraphQL\InputTypes\Utilities\Upload;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Media\MediaModelsTypeEnum;
use App\GraphQL\Types\NonNullType;

class UploadMultiLangType extends BaseInputType
{
    public const NAME = 'UploadMultiLangType';

    public function fields(): array
    {
        return [
            'model_id' => [
                'type' => NonNullType::id()
            ],
            'model_type' => [
                'type' => MediaModelsTypeEnum::nonNullType(),
            ],
            'files' => [
                'type' => MultiLangFileType::nonNullList()
            ]
        ];
    }
}
