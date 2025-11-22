<?php


namespace Tests\Feature\Queries\BackOffice\Chat\Menu;


use App\Enums\Chat\ChatMenuActionEnum;
use App\GraphQL\Queries\BackOffice\Chat\Menu\ChatMenusQuery;
use App\Models\Chat\ChatMenu;
use App\Models\Chat\ChatMenuTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ChatMenusQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsSuperAdmin();
    }

    public function test_get_list(): void
    {
        $chatMenu1 = ChatMenu::factory()
            ->redirectAction()
            ->create();

        $chatMenu2 = ChatMenu::factory()
            ->create();

        $chatMenu3 = ChatMenu::factory()
            ->subMenu()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(ChatMenusQuery::NAME)
                ->args(
                    [
                        'sort' => null
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                            'action',
                            'redirect_to',
                            'sub_menu' => [
                                'id',
                                'action'
                            ],
                            'active_sub_menu' => [
                                'id',
                                'action'
                            ],
                            'active',
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
                            'updated_at',
                            'created_at',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenusQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $chatMenu1->id,
                                    'action' => $chatMenu1->action,
                                    'redirect_to' => $chatMenu1->redirect_to,
                                    'sub_menu' => [],
                                    'active_sub_menu' => [],
                                    'active' => true,
                                    'translation' => [
                                        'id' => $chatMenu1->translation->id,
                                        'language' => $chatMenu1->translation->language,
                                        'title' => $chatMenu1->translation->title
                                    ],
                                    'translations' => $chatMenu1
                                        ->translations
                                        ->map(
                                            fn(ChatMenuTranslation $translation) => [
                                                'id' => $translation->id,
                                                'language' => $translation->language,
                                                'title' => $translation->title
                                            ]
                                        )
                                        ->toArray(),
                                    'updated_at' => $chatMenu1->created_at,
                                    'created_at' => $chatMenu1->created_at,
                                ],
                                [
                                    'id' => $chatMenu2->id,
                                    'action' => $chatMenu2->action,
                                    'redirect_to' => null,
                                    'sub_menu' => [],
                                    'active_sub_menu' => [],
                                    'active' => true,
                                    'translation' => [
                                        'id' => $chatMenu2->translation->id,
                                        'language' => $chatMenu2->translation->language,
                                        'title' => $chatMenu2->translation->title
                                    ],
                                    'translations' => $chatMenu2
                                        ->translations
                                        ->map(
                                            fn(ChatMenuTranslation $translation) => [
                                                'id' => $translation->id,
                                                'language' => $translation->language,
                                                'title' => $translation->title
                                            ]
                                        )
                                        ->toArray(),
                                    'updated_at' => $chatMenu2->created_at,
                                    'created_at' => $chatMenu2->created_at,
                                ],
                                [
                                    'id' => $chatMenu3->id,
                                    'action' => $chatMenu3->action,
                                    'redirect_to' => null,
                                    'sub_menu' => $chatMenu3
                                        ->subMenu
                                        ->map(
                                            fn(ChatMenu $subMenu) => [
                                                'id' => $subMenu->id,
                                                'action' => $subMenu->action
                                            ]
                                        )
                                        ->toArray(),
                                    'active_sub_menu' => $chatMenu3
                                        ->subMenu
                                        ->map(
                                            fn(ChatMenu $subMenu) => [
                                                'id' => $subMenu->id,
                                                'action' => $subMenu->action
                                            ]
                                        )
                                        ->toArray(),
                                    'active' => true,
                                    'translation' => [
                                        'id' => $chatMenu3->translation->id,
                                        'language' => $chatMenu3->translation->language,
                                        'title' => $chatMenu3->translation->title
                                    ],
                                    'translations' => $chatMenu3
                                        ->translations
                                        ->map(
                                            fn(ChatMenuTranslation $translation) => [
                                                'id' => $translation->id,
                                                'language' => $translation->language,
                                                'title' => $translation->title
                                            ]
                                        )
                                        ->toArray(),
                                    'updated_at' => $chatMenu3->created_at,
                                    'created_at' => $chatMenu3->created_at,
                                ],
                                [
                                    'id' => $chatMenu3->subMenu[0]->id,
                                    'action' => $chatMenu3->subMenu[0]->action,
                                    'redirect_to' => null,
                                    'sub_menu' => [],
                                    'active_sub_menu' => [],
                                    'active' => true,
                                    'translation' => [
                                        'id' => $chatMenu3->subMenu[0]->translation->id,
                                        'language' => $chatMenu3->subMenu[0]->translation->language,
                                        'title' => $chatMenu3->subMenu[0]->translation->title
                                    ],
                                    'translations' => $chatMenu3->subMenu[0]
                                        ->translations
                                        ->map(
                                            fn(ChatMenuTranslation $translation) => [
                                                'id' => $translation->id,
                                                'language' => $translation->language,
                                                'title' => $translation->title
                                            ]
                                        )
                                        ->toArray(),
                                    'updated_at' => $chatMenu3->subMenu[0]->created_at,
                                    'created_at' => $chatMenu3->subMenu[0]->created_at,
                                ],
                                [
                                    'id' => $chatMenu3->subMenu[1]->id,
                                    'action' => $chatMenu3->subMenu[1]->action,
                                    'redirect_to' => null,
                                    'sub_menu' => [],
                                    'active_sub_menu' => [],
                                    'active' => true,
                                    'translation' => [
                                        'id' => $chatMenu3->subMenu[1]->translation->id,
                                        'language' => $chatMenu3->subMenu[1]->translation->language,
                                        'title' => $chatMenu3->subMenu[1]->translation->title
                                    ],
                                    'translations' => $chatMenu3->subMenu[1]
                                        ->translations
                                        ->map(
                                            fn(ChatMenuTranslation $translation) => [
                                                'id' => $translation->id,
                                                'language' => $translation->language,
                                                'title' => $translation->title
                                            ]
                                        )
                                        ->toArray(),
                                    'updated_at' => $chatMenu3->subMenu[1]->created_at,
                                    'created_at' => $chatMenu3->subMenu[1]->created_at,
                                ],
                                [
                                    'id' => $chatMenu3->subMenu[2]->id,
                                    'action' => $chatMenu3->subMenu[2]->action,
                                    'redirect_to' => null,
                                    'sub_menu' => [],
                                    'active_sub_menu' => [],
                                    'active' => true,
                                    'translation' => [
                                        'id' => $chatMenu3->subMenu[2]->translation->id,
                                        'language' => $chatMenu3->subMenu[2]->translation->language,
                                        'title' => $chatMenu3->subMenu[2]->translation->title
                                    ],
                                    'translations' => $chatMenu3->subMenu[2]
                                        ->translations
                                        ->map(
                                            fn(ChatMenuTranslation $translation) => [
                                                'id' => $translation->id,
                                                'language' => $translation->language,
                                                'title' => $translation->title
                                            ]
                                        )
                                        ->toArray(),
                                    'updated_at' => $chatMenu3->subMenu[2]->created_at,
                                    'created_at' => $chatMenu3->subMenu[2]->created_at,
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(6, 'data.' . ChatMenusQuery::NAME . '.data');
    }

    public function test_get_menu_item_by_action()
    {
        ChatMenu::factory()
            ->redirectAction()
            ->create();

        ChatMenu::factory()
            ->create();

        $chatMenu3 = ChatMenu::factory()
            ->subMenuWithoutSubMenu()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(ChatMenusQuery::NAME)
                ->args(
                    [
                        'action' => ChatMenuActionEnum::SUB_MENU()
                    ]
                )
                ->select(
                    [
                        'data' => [
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
                        ChatMenusQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $chatMenu3->id,
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }
}
