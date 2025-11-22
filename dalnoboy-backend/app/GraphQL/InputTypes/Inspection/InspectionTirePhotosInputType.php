<?php

namespace App\GraphQL\InputTypes\Inspection;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Inspections\TirePhotoTypeEnumType;
use App\GraphQL\Types\NonNullType;

class InspectionTirePhotosInputType extends BaseInputType
{
    public const NAME = 'InspectionTirePhotosInputType';

    public function fields(): array
    {
        return [
            'type' => [
                'type' => TirePhotoTypeEnumType::nonNullType(),
                'rules' => ['required', 'string']
            ],
            'file_as_base_64' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string']
            ],
            'file_name' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string']
            ],
            'file_ext' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string']
            ],
        ];
    }
}
