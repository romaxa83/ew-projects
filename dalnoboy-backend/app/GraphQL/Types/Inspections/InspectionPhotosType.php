<?php


namespace App\GraphQL\Types\Inspections;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\Models\Inspections\Inspection;

class InspectionPhotosType extends BaseType
{
    public const NAME = 'InspectionPhotosType';

    public function fields(): array
    {
        return [
            Inspection::MC_STATE_NUMBER => [
                'type' => MediaType::nonNullType(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(Inspection $inspection) => $inspection->getFirstMedia(Inspection::MC_STATE_NUMBER)
            ],
            Inspection::MC_VEHICLE => [
                'type' => MediaType::nonNullType(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(Inspection $inspection) => $inspection->getFirstMedia(Inspection::MC_VEHICLE)
            ],
            Inspection::MC_DATA_SHEET_1 => [
                'type' => MediaType::type(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(Inspection $inspection) => $inspection->getFirstMedia(Inspection::MC_DATA_SHEET_1)
            ],
            Inspection::MC_DATA_SHEET_2 => [
                'type' => MediaType::type(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(Inspection $inspection) => $inspection->getFirstMedia(Inspection::MC_DATA_SHEET_2)
            ],
            Inspection::MC_ODO => [
                'type' => MediaType::type(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(Inspection $inspection) => $inspection->getFirstMedia(Inspection::MC_ODO)
            ],
            Inspection::MC_SIGN => [
                'type' => MediaType::type(),
                'is_relation' => false,
                'selectable' => false,
                'resolve' => fn(Inspection $inspection) => $inspection->getFirstMedia(Inspection::MC_SIGN)
            ],
        ];
    }
}
