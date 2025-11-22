<?php

namespace Feature\Mutations\BackOffice\Menu;

use App\GraphQL\Mutations\BackOffice\Menu\MenuToggleActiveMutation;
use App\Models\About\Page;
use App\Models\Menu\Menu;
use App\Permissions\Menu\MenuUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class MenuToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([MenuUpdatePermission::KEY]);
    }

    public function test_toggle_menu(): void
    {
        $menu = Menu::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(MenuToggleActiveMutation::NAME)
                ->args(
                    [
                        'id' => $menu->id
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        MenuToggleActiveMutation::NAME => [
                            'id',
                            'active',
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        MenuToggleActiveMutation::NAME => [
                            'id' => $menu->id,
                            'active' => false,
                        ]
                    ]
                ]
            );
    }

    public function test_toggle_menu_on_active(): void
    {
        $menu = Menu::factory(
            [
                'active' => false,
                'page_id' => Page::factory(['active' => false])
            ]
        )
            ->create();

        $this->assertFalse($menu->active);
        $this->assertFalse($menu->page->active);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(MenuToggleActiveMutation::NAME)
                ->args(
                    [
                        'id' => $menu->id,
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'page' => [
                            'active'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        MenuToggleActiveMutation::NAME => [
                            'id',
                            'active',
                            'page' => [
                                'active'
                            ],
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        MenuToggleActiveMutation::NAME => [
                            'id' => $menu->id,
                            'active' => true,
                            'page' => [
                                'active' => true
                            ]
                        ]
                    ]
                ]
            );
    }
}
