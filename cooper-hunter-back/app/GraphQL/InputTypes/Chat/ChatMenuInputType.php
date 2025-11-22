<?php


namespace App\GraphQL\InputTypes\Chat;


use App\Enums\Chat\ChatMenuActionEnum;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Chat\ChatMenuActionEnumType;
use App\GraphQL\Types\Enums\Chat\ChatMenuActionRedirectEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Chat\ChatMenu;
use App\Rules\Chat\SubMenuRule;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class ChatMenuInputType extends BaseInputType
{
    public const NAME = 'ChatMenuInputType';

    public function fields(): array
    {
        return [
            'action' => [
                'type' => ChatMenuActionEnumType::nonNullType()
            ],
            'redirect_to' => [
                'type' => ChatMenuActionRedirectEnumType::type(),
                'description' => 'Required if chat_menu.action is REDIRECT',
                'rules' => [
                    'nullable',
                    'string',
                    'required_if:chat_menu.action,' . ChatMenuActionEnum::REDIRECT
                ]
            ],
            'parent_menu_item_id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    'int',
                    Rule::exists(ChatMenu::class, 'id')
                        ->where('action', ChatMenuActionEnum::SUB_MENU)
                ]
            ],
            'sub_menu' => [
                'type' => Type::listOf(
                    NonNullType::id()
                ),
                'rules' => [
                    'nullable',
                    'array',
                    new SubMenuRule()
                ]
            ],
            'active' => [
                'type' => NonNullType::boolean(),
                'defaultValue' => true,
            ],
            'translations' => [
                'type' => ChatMenuTranslationInputType::nonNullList(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator()
                ],
            ]
        ];
    }
}
