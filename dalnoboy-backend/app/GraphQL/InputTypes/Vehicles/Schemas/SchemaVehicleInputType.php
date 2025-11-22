<?php


namespace App\GraphQL\InputTypes\Vehicles\Schemas;


use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Schemas\SchemaWheel;
use Illuminate\Validation\Rule;

class SchemaVehicleInputType extends BaseInputType
{
    public const NAME = 'SchemaVehicleInputType';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => NonNullType::string(),
            ],
            'original_schema_id' => [
                'type' => NonNullType::id(),
                'description' => 'ID of scheme that was the basis for create new scheme. If it is update mutation you should specify value the same of "id"',
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(SchemaVehicle::class, 'id')
                ]
            ],
            'wheels' => [
                'type' => NonNullType::listOfId(),
                'description' => 'List wheel ids from original scheme which are turned on in this scheme',
                'rules' => [
                    'required',
                    'array',
                    Rule::exists(SchemaWheel::class, 'id')
                ],
            ]
        ];
    }
}
