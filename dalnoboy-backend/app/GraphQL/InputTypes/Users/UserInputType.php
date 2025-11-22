<?php


namespace App\GraphQL\InputTypes\Users;


use App\Enums\Permissions\GuardsEnum;
use App\GraphQL\InputTypes\PhoneInputType;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Localization\LanguageEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Branches\Branch;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class UserInputType extends BaseInputType
{
    public const NAME = 'UserInputType';

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
            'phones' => [
                'type' => PhoneInputType::nonNullList(),
            ],
            'email' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'email',
                ]
            ],
            'role_id' => [
                'type' => NonNullType::id(),
                'description' => 'Role id for guard: ' . GuardsEnum::USER,
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Role::class, 'id')
                        ->where('guard_name', GuardsEnum::USER)
                ]
            ],
            'language' => [
                'type' => LanguageEnumType::nonNullType(),
                'defaultValue' => Language::default()
                    ->first()->slug
            ],
            'branch_id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    'int',
                    Rule::exists(Branch::class, 'id')
                ]
            ]
        ];
    }
}
