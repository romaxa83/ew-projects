<?php


namespace App\GraphQL\Types\Chat;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Chat\ChatMenuActionEnumType;
use App\GraphQL\Types\Enums\Chat\ChatMenuActionRedirectEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Chat\ChatMenu;

class ChatMenuType extends BaseType
{
    public const NAME = 'ChatMenuType';
    public const MODEL = ChatMenu::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'action' => [
                    'type' => ChatMenuActionEnumType::nonNullType(),
                ],
                'redirect_to' => [
                    'type' => ChatMenuActionRedirectEnumType::type(),
                    'description' => 'Exists only if action REDIRECT',
                ],
                'parent_item' => [
                    'type' => self::type(),
                    'is_relation' => true,
                    'alias' => 'parent'
                ],
                'sub_menu' => [
                    'type' => self::list(),
                    'description' => 'Exists only if action SUB_MENU',
                    'is_relation' => true,
                    'alias' => 'subMenu',
                    'query' => fn(array $args, $query, $ctx) => $query
                        ->orderByDesc('sort'),
                ],
                'active_sub_menu' => [
                    'type' => self::list(),
                    'description' => 'Active sub menu items. Exists only if action SUB_MENU',
                    'is_relation' => true,
                    'alias' => 'activeSubMenu',
                    'query' => fn(array $args, $query, $ctx) => $query
                        ->orderByDesc('sort'),
                ],
                'active' => [
                    'type' => NonNullType::boolean()
                ],
                'translation' => [
                    'type' => ChatMenuTranslationType::nonNullType(),
                    'is_relation' => true,
                ],
                'translations' => [
                    'type' => ChatMenuTranslationType::nonNullList(),
                    'is_relation' => true,
                ]
            ]
        );
    }
}
