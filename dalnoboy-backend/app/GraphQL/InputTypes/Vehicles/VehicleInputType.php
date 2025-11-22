<?php


namespace App\GraphQL\InputTypes\Vehicles;


use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Clients\Client;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Dictionaries\VehicleModel;
use App\Models\Dictionaries\VehicleType;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class VehicleInputType extends BaseInputType
{
    public const NAME = 'VehicleInputType';

    public function fields(): array
    {

        if (!isBackOffice()) {
            $odoRules = [
                'int',
                'required_if:vehicle.form,' . VehicleFormEnum::MAIN
            ];
        } else {
            $odoRules = [
                'nullable',
                'int'
            ];
        }

        return [
            'state_number' => [
                'type' => NonNullType::string(),
                'description' => 'Regex: /^[a-z0-9\p{L}]+$/ui',
                'rules' => [
                    'required',
                    'string',
                    'regex:/^[a-z0-9\p{L}]+$/ui'
                ]
            ],
            'vin' => [
                'type' => Type::string(),
                'description' => 'Regex: /^[a-hj-npr-z0-9]{13}\d{4}$/i',
                'rules' => [
                    'nullable',
                    'string',
                    'regex:/^[a-hj-npr-z0-9]{13}\d{4}$/i'
                ]
            ],
            'is_moderated' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
                'description' => 'For FrontOffice only false - field does not depend on value'
            ],
            'form' => [
                'type' => VehicleFormEnumType::nonNullType(),
            ],
            'class_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(VehicleClass::class, 'id')
                ]
            ],
            'type_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(VehicleType::class, 'id')
                ]
            ],
            'make_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(VehicleMake::class, 'id')
                ]
            ],
            'model_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(VehicleModel::class, 'id')
                ]
            ],
            'client_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Client::class, 'id')
                ]
            ],
            'schema_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(SchemaVehicle::class, 'id')
                        ->where('is_default', false)
                ]
            ],
            'odo' => [
                'type' => Type::int(),
                'rules' => $odoRules,
            ],
            'active' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
                'rules' => [
                    'required',
                    'bool'
                ]
            ]
        ];
    }
}
