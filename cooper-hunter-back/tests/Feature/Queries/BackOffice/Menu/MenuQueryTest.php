<?php

namespace Tests\Feature\Queries\BackOffice\Menu;

use App\Enums\Menu\MenuBlockEnum;
use App\Enums\Menu\MenuPositionEnum;
use App\GraphQL\Queries\BackOffice\Menu\MenuQuery;
use App\Models\Menu\Menu;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MenuQueryTest extends TestCase
{
    use DatabaseTransactions;

    private Collection $menus;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdmin();

        Menu::factory()
            ->count(1)
            ->create();
        Menu::factory(['position' => MenuPositionEnum::FOOTER])
            ->count(2)
            ->create();
        Menu::factory(['active' => false])
            ->count(2)
            ->create();

        $this->menus = Menu::all();
    }

    public function test_get_menu(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(MenuQuery::NAME)
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        MenuQuery::NAME => [
                            '*' => [
                                'id'
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        MenuQuery::NAME => [
                            [
                                'id' => $this->menus[4]->id
                            ],
                            [
                                'id' => $this->menus[3]->id
                            ],
                            [
                                'id' => $this->menus[2]->id
                            ],
                            [
                                'id' => $this->menus[1]->id
                            ],
                            [
                                'id' => $this->menus[0]->id
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(5, 'data.' . MenuQuery::NAME);
    }

    public function test_menu_filter_id(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(MenuQuery::NAME)
                ->args(
                    [
                        'id' => $this->menus[2]->id
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        MenuQuery::NAME => [
                            [
                                'id' => $this->menus[2]->id
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . MenuQuery::NAME);
    }

    public function test_menu_filter_active(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(MenuQuery::NAME)
                ->args(
                    [
                        'published' => false
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        MenuQuery::NAME => [
                            [
                                'id' => $this->menus[4]->id
                            ],
                            [
                                'id' => $this->menus[3]->id
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . MenuQuery::NAME);
    }

    public function test_menu_filter_position(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(MenuQuery::NAME)
                ->args(
                    [
                        'position' => MenuPositionEnum::FOOTER()
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        MenuQuery::NAME => [
                            [
                                'id' => $this->menus[2]->id
                            ],
                            [
                                'id' => $this->menus[1]->id
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . MenuQuery::NAME);
    }

    public function test_menu_filter_block(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(MenuQuery::NAME)
                ->args(
                    [
                        'block' => MenuBlockEnum::OTHER()
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        MenuQuery::NAME => [
                            [
                                'id' => $this->menus[4]->id
                            ],
                            [
                                'id' => $this->menus[3]->id
                            ],
                            [
                                'id' => $this->menus[2]->id
                            ],
                            [
                                'id' => $this->menus[1]->id
                            ],
                            [
                                'id' => $this->menus[0]->id
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(5, 'data.' . MenuQuery::NAME);
    }

    public function test_menu_filter_page_id(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(MenuQuery::NAME)
                ->args(
                    [
                        'page_id' => $this->menus[0]->page->id
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        MenuQuery::NAME => [
                            [
                                'id' => $this->menus[0]->id
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . MenuQuery::NAME);
    }

    public function test_menu_filter_title(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(MenuQuery::NAME)
                ->args(
                    [
                        'query' => $this->menus[4]->translation->title
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        MenuQuery::NAME => [
                            [
                                'id' => $this->menus[4]->id
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . MenuQuery::NAME);
    }
}
