<?php


namespace App\GraphQL\InputTypes\Admins;


use App\Enums\Permissions\GuardsEnum;
use App\GraphQL\InputTypes\PhoneInputType;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Localization\LanguageEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class AdminInputType extends BaseInputType
{
    public const NAME = 'AdminInputType';

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
            'password' => [
                'type' => Type::string(),
                'description' => 'Admin password. Min length - 8 symbols. Can be automatic generate',
                'rules' => [
                    'nullable',
                    'string',
                    'min:8'
                ]
            ],
            'role_id' => [
                'type' => NonNullType::id(),
                'description' => 'Role id for guard: ' . GuardsEnum::ADMIN,
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Role::class, 'id')
                        ->where('guard_name', GuardsEnum::ADMIN)
                ]
            ],
            'language' => [
                'type' => LanguageEnumType::nonNullType(),
                'defaultValue' => Language::default()
                    ->first()->slug
            ],
        ];
    }
}
