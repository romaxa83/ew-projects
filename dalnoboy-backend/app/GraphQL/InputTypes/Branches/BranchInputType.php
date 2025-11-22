<?php


namespace App\GraphQL\InputTypes\Branches;


use App\GraphQL\InputTypes\PhoneInputType;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Locations\Region;
use Illuminate\Validation\Rule;

class BranchInputType extends BaseInputType
{
    public const NAME = 'BranchInputType';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => NonNullType::string(),
            ],
            'city' => [
                'type' => NonNullType::string(),
            ],
            'region_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Region::class, 'id')
                ]
            ],
            'address' => [
                'type' => NonNullType::string(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
            ],
            'phones' => [
                'type' => PhoneInputType::nonNullList(),
            ],
        ];
    }
}
