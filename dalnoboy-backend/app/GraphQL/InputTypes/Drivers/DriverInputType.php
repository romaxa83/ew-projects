<?php


namespace App\GraphQL\InputTypes\Drivers;


use App\GraphQL\InputTypes\PhoneInputType;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Clients\Client;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class DriverInputType extends BaseInputType
{
    public const NAME = 'DriverInputType';

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
            'email' => [
                'type' => Type::string(),
                'rules' => [
                    'nullable',
                    'email'
                ]
            ],
            'phones' => [
                'type' => PhoneInputType::nonNullList(),
            ],
            'comment' => [
                'type' => Type::string(),
            ],
            'is_moderated' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
            ],
            'active' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
            ],
            'client_id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    'int',
                    Rule::exists(Client::class, 'id')
                ]
            ],
        ];
    }
}
