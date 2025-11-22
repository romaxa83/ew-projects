<?php

namespace App\GraphQL\InputTypes\Companies;

use App\Models\Companies\Corporation;
use App\Rules\Companies\CompanyUniqEmailRule;
use App\Rules\Phone\PhoneUniqRule;
use App\Rules\PhoneRule;
use Illuminate\Validation\Rule;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Companies\CompanyTypeEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\Company;
use Core\Traits\GraphQL\Inputs\AddressInputTrait;
use GraphQL\Type\Definition\Type;

class CompanyInput extends BaseInputType
{
    use AddressInputTrait;

    public const NAME = 'CompanyInput';

    public function fields(): array
    {

        return array_merge([
            'corporation_id' => [
                'type' => Type::id(),
                'rules' => ['nullable', 'int', Rule::exists(Corporation::class, 'id')],
            ],
            'business_name' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string']
            ],
            'email' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'string',
                    'email:filter',
                    new CompanyUniqEmailRule()
//                    Rule::unique(Company::TABLE, 'email')
                ]
            ],
            'phone' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string', new PhoneRule(), new PhoneUniqRule(Company::TABLE)]
            ],
            'fax' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', new PhoneRule(), new PhoneUniqRule(Company::TABLE, 'fax')]
            ],
            'taxpayer_id' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string', Rule::unique(Company::TABLE, 'taxpayer_id')]
            ],
            'tax' => [
                'type' => Type::string(),
            ],
            'websites' => [
                'type' => Type::listOf(Type::string()),
            ],
            'marketplaces' => [
                'type' => Type::listOf(Type::string()),
            ],
            'trade_names' => [
                'type' => Type::listOf(Type::string()),
            ],
            'type' => [
                'type' => CompanyTypeEnumType::nonNullType(),
            ],
        ],
        $this->addressFields(),
        );
    }
}

