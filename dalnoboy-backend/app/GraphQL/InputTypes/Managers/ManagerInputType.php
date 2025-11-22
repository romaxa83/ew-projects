<?php


namespace App\GraphQL\InputTypes\Managers;


use App\GraphQL\InputTypes\PhoneInputType;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\Region;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class ManagerInputType extends BaseInputType
{
    public const NAME = 'ManagerInputType';

    public function fields(): array
    {
        return [
            'first_name' => [
                'type' => NonNullType::string(),
            ],
            'last_name' => [
                'type' => NonNullType::string(),
            ],
            'second_name' => [
                'type' => Type::string(),
            ],
            'region_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Region::class, 'id')
                ]
            ],
            'city' => [
                'type' => NonNullType::string(),
            ],
            'phones' => [
                'type' => PhoneInputType::nonNullList(),
            ],
        ];
    }
}
