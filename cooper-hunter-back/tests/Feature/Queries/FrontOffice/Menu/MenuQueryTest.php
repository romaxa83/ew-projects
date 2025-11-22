<?php

namespace Tests\Feature\Queries\FrontOffice\Menu;

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
        $this->postGraphQL(
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
            ->assertJsonCount(3, 'data.' . MenuQuery::NAME);
    }

    public function test_menu_filter_id(): void
    {
        $this->postGraphQL(
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

    public function test_try_to_get_non_active_menu(): void
    {
        $this->postGraphQL(
            GraphQLQuery::query(MenuQuery::NAME)
                ->args(
                    [
                        'id' => $this->menus[4]->id
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
                        ]
                    ]
                ]
            )
            ->assertJsonCount(0, 'data.' . MenuQuery::NAME);
    }

    public function test_menu_filter_position(): void
    {
        $this->postGraphQL(
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
        $this->postGraphQL(
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
            ->assertJsonCount(3, 'data.' . MenuQuery::NAME);
    }

    public function test_menu_filter_title(): void
    {
        $this->postGraphQL(
            GraphQLQuery::query(MenuQuery::NAME)
                ->args(
                    [
                        'query' => $this->menus[1]->translation->title
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
                                'id' => $this->menus[1]->id
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . MenuQuery::NAME);
    }

    public function test_get_empty_menu(): void
    {
        Menu::query()
            ->delete();

        $this->postGraphQL(
            GraphQLQuery::query(MenuQuery::NAME)
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
                        ]
                    ]
                ]
            )
            ->assertJsonCount(0, 'data.' . MenuQuery::NAME);
    }
}
