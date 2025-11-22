<?php

namespace App\GraphQL\InputTypes\Companies;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\PhoneRule;
use Core\Traits\GraphQL\Inputs\AddressInputTrait;
use GraphQL\Type\Definition\Type;

class ShippingAddressInput extends BaseInputType
{
    use AddressInputTrait;

    public const NAME = 'CompanyShippingAddressInput';

    public function fields(): array
    {
        return array_merge([
            'id' => [
                'type' => Type::id(),
                'description' => 'Передавать при обновлении данной сущьности'
            ],
            'name' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string']
            ],
            'active' => [
                'type' => Type::boolean(),
            ],
            'phone' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string', new PhoneRule()]
            ],
            'fax' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', new PhoneRule()]
            ],
            'email' => [
                'type' => Type::string(),
                'rules' => ['required', 'string', 'email:filter'],
            ],
            'receiving_persona' => [
                'type' => Type::string(),
                'rules' => ['required', 'string']
            ],
        ],
            $this->addressFields(),
        );
    }
}

