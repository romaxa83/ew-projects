<?php

namespace App\GraphQL\Types\Inspections;

use App\Enums\Inspections\TirePhotoType;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\Models\Inspections\InspectionTire;

class InspectionTirePhotosType extends BaseType
{
    public const NAME = 'InspectionTirePhotosType';

    public function fields(): array
    {
        return [
            TirePhotoType::MAIN => [
                'type' => MediaType::Type(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(InspectionTire $m) => $m->getFirstMedia(TirePhotoType::MAIN)
            ],
            TirePhotoType::SERIAL_NUMBER => [
                'type' => MediaType::Type(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(InspectionTire $m) => $m->getFirstMedia(TirePhotoType::SERIAL_NUMBER)
            ],
        ];
    }
}
