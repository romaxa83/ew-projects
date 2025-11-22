<?php


namespace Tests\Feature\Queries\FrontOffice\Chat\Menu;


use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Queries\FrontOffice\Chat\Menu\ChatMenusQuery;
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

        $this->loginAsTechnicianWithRole();
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
            ->has(
                ChatMenu::factory(['active' => false]),
                'subMenu'
            )
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(ChatMenusQuery::NAME)
                ->select(
                    [
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
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenusQuery::NAME => [
                            [
                                'id' => $chatMenu3->id,
                                'action' => $chatMenu3->action->value,
                                'redirect_to' => null,
                                'sub_menu' => $chatMenu3
                                    ->subMenu
                                    ->map(
                                        fn(ChatMenu $subMenu) => [
                                            'id' => $subMenu->id,
                                            'action' => $subMenu->action->value
                                        ]
                                    )
                                    ->reverse()
                                    ->values()
                                    ->toArray(),
                                'active_sub_menu' => $chatMenu3
                                    ->subMenu
                                    ->filter(
                                        fn(ChatMenu $subMenu) => $subMenu->active
                                    )
                                    ->map(
                                        fn(ChatMenu $subMenu) => [
                                            'id' => $subMenu->id,
                                            'action' => $subMenu->action->value
                                        ]
                                    )
                                    ->reverse()
                                    ->values()
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
                                'updated_at' => $chatMenu3->created_at->format(DatetimeEnum::DEFAULT_FORMAT),
                                'created_at' => $chatMenu3->created_at->format(DatetimeEnum::DEFAULT_FORMAT),
                            ],
                            [
                                'id' => $chatMenu2->id,
                                'action' => $chatMenu2->action->value,
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
                                'updated_at' => $chatMenu2->created_at->format(DatetimeEnum::DEFAULT_FORMAT),
                                'created_at' => $chatMenu2->created_at->format(DatetimeEnum::DEFAULT_FORMAT),
                            ],
                            [
                                'id' => $chatMenu1->id,
                                'action' => $chatMenu1->action->value,
                                'redirect_to' => $chatMenu1->redirect_to->value,
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
                                'updated_at' => $chatMenu1->created_at->format(DatetimeEnum::DEFAULT_FORMAT),
                                'created_at' => $chatMenu1->created_at->format(DatetimeEnum::DEFAULT_FORMAT),
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . ChatMenusQuery::NAME);
    }

    public function test_get_without_empty_sub_menu_list(): void
    {
        $chatMenu1 = ChatMenu::factory()
            ->redirectAction()
            ->create();

        $chatMenu2 = ChatMenu::factory()
            ->create();

        ChatMenu::factory()
            ->subMenuWithoutSubMenu()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(ChatMenusQuery::NAME)
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenusQuery::NAME => [
                            [
                                'id' => $chatMenu2->id,
                            ],
                            [
                                'id' => $chatMenu1->id,
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . ChatMenusQuery::NAME);
    }

    public function test_sub_menu_sorting(): void
    {
        $chatMenu = ChatMenu::factory()
            ->subMenuWithoutSubMenu()
            ->has(
                ChatMenu::factory()->count(5),
                'subMenu'
            )
            ->create();

        $subMenus = $chatMenu->subMenu;

        $sub1 = $subMenus->shift();
        $sub2 = $subMenus->shift();
        $sub3 = $subMenus->shift();
        $sub4 = $subMenus->shift();
        $sub5 = $subMenus->shift();

        $sub5->update(['sort' => 10]);
        $sub4->update(['sort' => 9]);
        $sub3->update(['sort' => 8]);
        $sub2->update(['sort' => 7]);
        $sub1->update(['sort' => 6]);

        $this->postGraphQL(
            GraphQLQuery::query(ChatMenusQuery::NAME)
                ->select(
                    [
                        'id',
                        'active_sub_menu' => [
                            'id',
                            'translation' => [
                                'title',
                                'language',
                            ]
                        ],
                        'sub_menu' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenusQuery::NAME => [
                            [
                                'id' => $chatMenu->id,
                                'active_sub_menu' => $subItems = [
                                    [
                                        'id' => $sub5->id,
                                    ],
                                    [
                                        'id' => $sub4->id,
                                    ],
                                    [
                                        'id' => $sub3->id,
                                    ],
                                    [
                                        'id' => $sub2->id,
                                    ],
                                    [
                                        'id' => $sub1->id,
                                    ],
                                ],
                                'sub_menu' => $subItems,
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . ChatMenusQuery::NAME);
    }
}
