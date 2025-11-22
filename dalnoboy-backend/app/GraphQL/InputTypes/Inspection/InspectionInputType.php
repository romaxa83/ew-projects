<?php


namespace App\GraphQL\InputTypes\Inspection;


use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\InspectionReason;
use App\Models\Drivers\Driver;
use App\Models\Vehicles\Vehicle;
use GraphQL\Type\Definition\Type;

class InspectionInputType extends BaseInputType
{
    public const NAME = 'InspectionInputType';

    public function fields(): array
    {
        return [
            'vehicle_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Vehicle::ruleExists()
                ]
            ],
            'driver_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Driver::ruleExists()
                ]
            ],
            'odo' => [
                'type' => Type::int(),
                'description' => 'Required form MAIN vehicle form',
            ],
            'inspection_reason_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    InspectionReason::ruleExists()
                ]
            ],
            'inspection_reason_description' => [
                'type' => Type::string(),
                'description' => 'Required if inspection_reason.need_description',
            ],
            'unable_to_sign' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => false,
            ],
            'is_offline' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => false
            ],
            'photos' => [
                'type' => InspectionPhotosInputType::type(),
                'description' => 'Required if inspection is creating',
            ],
            'tires' => [
                'type' => InspectionTireInputType::nonNullList(),
            ],
            'time' => [
                'type' => NonNullType::int(),
                'description' => 'UNIX time',
            ]
        ];
    }
}
