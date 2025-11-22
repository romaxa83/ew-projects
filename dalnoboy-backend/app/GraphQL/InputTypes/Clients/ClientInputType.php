<?php


namespace App\GraphQL\InputTypes\Clients;


use App\GraphQL\InputTypes\PhoneInputType;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Managers\Manager;
use App\Rules\Clients\EDRPOURule;
use App\Rules\Clients\INNRule;
use App\Rules\Clients\OnlyINNOrEDRPOURule;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class ClientInputType extends BaseInputType
{
    public const NAME = 'ClientInputType';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => NonNullType::string(),
            ],
            'contact_person' => [
                'type' => NonNullType::string(),
            ],
            'manager_id' => [
                'type' => Type::id(),
                'description' => 'Field required in BackOffice',
                'rules' => [
                    Rule::requiredIf(
                        fn() => isBackOffice()
                    ),
                    'int',
                    Rule::exists(Manager::class, 'id')
                ]
            ],
            'edrpou' => [
                'type' => Type::string(),
                'description' => 'EDRPOU code required if empty INN. Only ONE field is required. Has to comply with these rules: https://1cinfo.com.ua/Article/Detail/Proverka_koda_po_EDRPOU/.' .
                    ' Regex: /^[1-9][0-9]{7}$/. Example: 32855961',
                'rules' => [
                    'required_without:client.inn',
                    'string',
                    'regex:/^[1-9][0-9]{7}$/',
                    new EDRPOURule()
                ]
            ],
            'inn' => [
                'type' => Type::string(),
                'description' => "INN code required if empty EDRPOU. Only ONE field is required. Regex: /^[1-9][0-9]{9}$/. \nAlgorithm to check Ukrainian INN (INN = ABCDEFGHIJ): \n1) X = A * (-1) + B * 5 + C * 7 + D * 9 + E * 4 + F * 6 + G * 10 + H * 5 + I * 7 \n2) Y = X%11 \n3) If Y > 9 -> Y = Y/10 \n4) Y === J. \n Example: 2245134075",
                'rules' => [
                    'required_without:client.edrpou',
                    'string',
                    'regex:/^[1-9][0-9]{9}$/',
                    new INNRule(),
                    new OnlyINNOrEDRPOURule()
                ]
            ],
            'phones' => [
                'type' => PhoneInputType::nonNullList(),
            ],
            'is_offline' => [
                'type' => NonNullType::boolean(),
                'description' => 'Use only for FrontOffice',
                'defaultValue' => false,
            ],
            'is_moderated' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
            ],
            'active' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
            ],
        ];
    }
}
