<?php

namespace App\GraphQL\InputTypes\Inspection;

use App\Enums\Inspections\TirePhotoType;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\FileType;

class InspectionTirePhotosInputAsFileType extends BaseInputType
{
    public const NAME = 'InspectionTirePhotosInputAsFileType';

    public function fields(): array
    {
        return [
            TirePhotoType::MAIN => [
                'type' => FileType::type(),
            ],
            TirePhotoType::SERIAL_NUMBER => [
                'type' => FileType::type(),
            ],
        ];
    }
}

