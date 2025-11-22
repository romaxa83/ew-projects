<?php


namespace App\GraphQL\Types\Inspections;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Branches\BranchType;
use App\GraphQL\Types\Dictionaries\InspectionReasonType;
use App\GraphQL\Types\Drivers\DriverType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserType;
use App\GraphQL\Types\Vehicles\VehicleType;
use App\Models\Inspections\Inspection;
use App\Models\Users\User;
use GraphQL\Type\Definition\Type;

class InspectionType extends BaseType
{
    public const NAME = 'InspectionType';
    public const MODEL = Inspection::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'id' => [
                    'type' => NonNullType::id(),
                    'always' => ['vehicle_id'],
                ],
                'inspector' => [
                    'type' => UserType::type(),
                    'is_relation' => true,
                ],
                'branch' => [
                    'type' => BranchType::type(),
                    'is_relation' => true,
                ],
                'vehicle' => [
                    'type' => VehicleType::nonNullType(),
                    'is_relation' => true,
                ],
                'driver' => [
                    'type' => DriverType::nonNullType(),
                    'is_relation' => true,
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
                'unable_to_sign' => [
                    'type' => NonNullType::boolean(),
                ],
                'inspection_reason' => [
                    'type' => InspectionReasonType::nonNullType(),
                    'is_relation' => true,
                    'alias' => 'inspectionReason'
                ],
                'inspection_reason_description' => [
                    'type' => Type::string(),
                ],
                'tires' => [
                    'type' => InspectionTireType::nonNullList(),
                    'is_relation' => true,
                    'alias' => 'inspectionTires'
                ],
                'photos' => [
                    'type' => InspectionPhotosType::nonNullType(),
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(Inspection $inspection) => $inspection
                ],
                'moderation_fields' => [
                    'type' => InspectionModerationFieldType::list(),
                    'description' => 'Fields list which were called moderation status (fields only from inspection)',
                    'is_relation' => false,
                ],
                'trailer_inspection' => [
                    'type' => self::type(),
                    'is_relation' => true,
                    'alias' => 'trailer'
                ],
                'main_inspection' => [
                    'type' => self::type(),
                    'is_relation' => true,
                    'alias' => 'main'
                ],
                'previous_inspection' => [
                    'type' => InspectionType::type(),
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => fn(Inspection $inspection) => $inspection->previousVehicleInspection()
                ],
                'odo' => [
                    'type' => Type::int(),
                ],
                'is_mine' => [
                    'type' => NonNullType::boolean(),
                    'is_relation' => false,
                    'selectable' => false,
                    'always' => ['inspector_id'],
                    'resolve' => fn(Inspection $inspection, array $args, mixed $context)
                        => ($context instanceof User && $inspection->inspector_id === $context->id),
                ],
                'has_relation' => [
                    'type' => NonNullType::boolean(),
                    'is_relation' => false,
                    'selectable' => false,
                    'always' => ['main_id'],
                    'resolve' => fn(Inspection $inspection) => $inspection->hasRelation(),
                ],
            ]
        );
    }
}
