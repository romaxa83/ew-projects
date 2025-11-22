<?php

namespace App\GraphQL\InputTypes\Menu;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\About\ForMemberPage;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class MenuOtherInput extends BaseInputType
{
    public const NAME = 'MenuOtherInput';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => Type::boolean(),
                'defaultValue' => true,
            ],
            'page_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(ForMemberPage::class, 'id')
                        ->where('for_member_type',)
                ]
            ],
            'translations' => [
                'type' => MenuTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
