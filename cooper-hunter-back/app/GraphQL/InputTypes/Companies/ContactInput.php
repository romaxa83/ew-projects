<?php

namespace App\GraphQL\InputTypes\Companies;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\PhoneRule;
use Core\Traits\GraphQL\Inputs\AddressInputTrait;

class ContactInput extends BaseInputType
{
    use AddressInputTrait;

    public const NAME = 'CompanyContactInput';

    public function fields(): array
    {
        return array_merge([
            'name' => [
                'type' => NonNullType::string(),
            ],
            'email' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string', 'email:filter'],
            ],
            'phone' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string', new PhoneRule()]
            ],
        ],
            $this->addressFields(),
        );
    }
}


