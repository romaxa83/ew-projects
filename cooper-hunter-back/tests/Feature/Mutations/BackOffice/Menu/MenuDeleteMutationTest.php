<?php

namespace Feature\Mutations\BackOffice\Menu;

use App\GraphQL\Mutations\BackOffice\Menu\MenuDeleteMutation;
use App\Models\Menu\Menu;
use App\Permissions\Menu\MenuDeletePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class MenuDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([MenuDeletePermission::KEY]);
    }

    public function test_delete_menu(): void
    {
        $menu = Menu::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(MenuDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $menu->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        MenuDeleteMutation::NAME
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        MenuDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Menu::class,
            [
                'id' => $menu->id
            ]
        );
    }
}
