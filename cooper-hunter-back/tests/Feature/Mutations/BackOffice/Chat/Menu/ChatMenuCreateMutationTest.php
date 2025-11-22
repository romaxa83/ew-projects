<?php


namespace Tests\Feature\Mutations\BackOffice\Chat\Menu;


use App\Enums\Chat\ChatMenuActionEnum;
use App\Enums\Chat\ChatMenuActionRedirectEnum;
use App\GraphQL\Mutations\BackOffice\Chat\Menu\ChatMenuCreateMutation;
use App\Models\Chat\ChatMenu;
use App\Models\Localization\Language;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatMenuCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsSuperAdmin();
    }

    public function test_create_chat_menu(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatMenuCreateMutation::NAME)
                ->args(
                    [
                        'chat_menu' => $chatMenu = [
                            'active' => true,
                            'action' => ChatMenuActionEnum::INFORMATION_FORM(),
                            'translations' => languages()
                                ->map(
                                    fn(Language $language) => [
                                        'language' => new EnumValue($language->slug),
                                        'title' => $this->faker->text
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'action',
                        'redirect_to',
                        'sub_menu' => [
                            'id'
                        ],
                        'active_sub_menu' => [
                            'id'
                        ],
                        'translation' => [
                            'id',
                            'language',
                            'title'
                        ],
                        'translations' => [
                            'id',
                            'language',
                            'title'
                        ],
                        'active',
                        'created_at',
                        'updated_at'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        ChatMenuCreateMutation::NAME => [
                            'id',
                            'action',
                            'redirect_to',
                            'sub_menu',
                            'active_sub_menu',
                            'translation' => [
                                'id',
                                'language',
                                'title'
                            ],
                            'translations' => [
                                '*' => [
                                    'id',
                                    'language',
                                    'title'
                                ]
                            ],
                            'active',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        ChatMenuCreateMutation::NAME => [
                            'action' => ChatMenuActionEnum::INFORMATION_FORM,
                            'redirect_to' => null,
                            'sub_menu' => [],
                            'active_sub_menu' => [],
                            'translations' => array_map(
                                fn(array $item) => [
                                    'language' => (string)$item['language'],
                                    'title' => $item['title']
                                ],
                                $chatMenu['translations']
                            ),
                            'active' => true
                        ]
                    ]
                ]
            );
    }

    public function test_create_redirect_chat_menu(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatMenuCreateMutation::NAME)
                ->args(
                    [
                        'chat_menu' => [
                            'active' => true,
                            'action' => ChatMenuActionEnum::REDIRECT(),
                            'redirect_to' => ChatMenuActionRedirectEnum::FIND_SOLUTION(),
                            'translations' => languages()
                                ->map(
                                    fn(Language $language) => [
                                        'language' => new EnumValue($language->slug),
                                        'title' => $this->faker->text
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'action',
                        'redirect_to',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenuCreateMutation::NAME => [
                            'action' => ChatMenuActionEnum::REDIRECT,
                            'redirect_to' => ChatMenuActionRedirectEnum::FIND_SOLUTION,
                        ]
                    ]
                ]
            );
    }

    public function test_create_sub_menu_chat_menu(): void
    {
        $subMenu = ChatMenu::factory()
            ->count(3)
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatMenuCreateMutation::NAME)
                ->args(
                    [
                        'chat_menu' => [
                            'active' => true,
                            'action' => ChatMenuActionEnum::SUB_MENU(),
                            'sub_menu' => $subMenu
                                ->pluck('id')
                                ->toArray(),
                            'translations' => languages()
                                ->map(
                                    fn(Language $language) => [
                                        'language' => new EnumValue($language->slug),
                                        'title' => $this->faker->text
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'action',
                        'sub_menu' => [
                            'id',
                            'action'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenuCreateMutation::NAME => [
                            'action' => ChatMenuActionEnum::SUB_MENU,
                            'sub_menu' => $subMenu
                                ->map(
                                    fn(ChatMenu $chatMenu) => [
                                        'id' => $chatMenu->id,
                                        'action' => $chatMenu->action
                                    ]
                                )
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_create_with_redirect_data_chat_menu(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatMenuCreateMutation::NAME)
                ->args(
                    [
                        'chat_menu' => [
                            'active' => true,
                            'action' => ChatMenuActionEnum::ONLINE_CHAT(),
                            'redirect_to' => ChatMenuActionRedirectEnum::FIND_SOLUTION(),
                            'translations' => languages()
                                ->map(
                                    fn(Language $language) => [
                                        'language' => new EnumValue($language->slug),
                                        'title' => $this->faker->text
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'action',
                        'redirect_to',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenuCreateMutation::NAME => [
                            'action' => ChatMenuActionEnum::ONLINE_CHAT,
                            'redirect_to' => null,
                        ]
                    ]
                ]
            );
    }

    public function test_create_sub_menu_chat_menu_without_sub_menu(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatMenuCreateMutation::NAME)
                ->args(
                    [
                        'chat_menu' => [
                            'active' => true,
                            'action' => ChatMenuActionEnum::SUB_MENU(),
                            'translations' => languages()
                                ->map(
                                    fn(Language $language) => [
                                        'language' => new EnumValue($language->slug),
                                        'title' => $this->faker->text
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'action',
                        'sub_menu' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenuCreateMutation::NAME => [
                            'action' => ChatMenuActionEnum::SUB_MENU,
                            'sub_menu' => null
                        ]
                    ]
                ]
            );
    }

    public function test_create_children_item_with_parent(): void
    {
        $parent = ChatMenu::factory()
            ->subMenu()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatMenuCreateMutation::NAME)
                ->args(
                    [
                        'chat_menu' => [
                            'active' => true,
                            'action' => ChatMenuActionEnum::INFORMATION_FORM(),
                            'parent_menu_item_id' => $parent->id,
                            'translations' => languages()
                                ->map(
                                    fn(Language $language) => [
                                        'language' => new EnumValue($language->slug),
                                        'title' => $this->faker->text
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'action',
                        'parent_item' => [
                            'id'
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenuCreateMutation::NAME => [
                            'action' => ChatMenuActionEnum::INFORMATION_FORM,
                            'parent_item' => [
                                'id' => $parent->id
                            ]
                        ]
                    ]
                ]
            );
    }
}
