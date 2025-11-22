<?php

namespace Feature\Mutations\BackOffice\Menu;

use App\Enums\Menu\MenuBlockEnum;
use App\Enums\Menu\MenuPositionEnum;
use App\GraphQL\Mutations\BackOffice\Menu\MenuUpdateMutation;
use App\Models\About\Page;
use App\Models\Menu\Menu;
use App\Permissions\Menu\MenuUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;
use Tests\Traits\TranslationHelper;

class MenuUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use TranslationHelper;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([MenuUpdatePermission::KEY]);
    }

    public function test_update_menu(): void
    {
        $menu = Menu::factory()
            ->create();
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(MenuUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $menu->id,
                        'menu' => [
                            'active' => false,
                            'position' => MenuPositionEnum::HEADER(),
                            'page_id' => $menu->page->id,
                            'block' => MenuBlockEnum::OTHER(),
                            'translations' => $this->getTranslationsArray(['title'])
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'position',
                        'active',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        MenuUpdateMutation::NAME => [
                            'id',
                            'active',
                            'position',
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        MenuUpdateMutation::NAME => [
                            'id' => $menu->id,
                            'active' => false,
                            'position' => MenuPositionEnum::HEADER
                        ]
                    ]
                ]
            );
    }

    public function test_update_menu_on_active(): void
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
            GraphQLQuery::mutation(MenuUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $menu->id,
                        'menu' => [
                            'active' => true,
                            'position' => MenuPositionEnum::FOOTER(),
                            'page_id' => $menu->page->id,
                            'block' => MenuBlockEnum::OTHER(),
                            'translations' => $this->getTranslationsArray(['title'])
                        ]
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
                        MenuUpdateMutation::NAME => [
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
                        MenuUpdateMutation::NAME => [
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
