<?php

namespace Feature\Mutations\BackOffice\About\Pages;

use App\GraphQL\Mutations\BackOffice\About\Pages\PageToggleActiveMutation;
use App\Models\About\Page;
use App\Models\Menu\Menu;
use App\Permissions\About\Pages\PageUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class PageToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    private Page $page;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([PageUpdatePermission::KEY]);

        $this->page = Page::factory()
            ->create();
    }

    public function test_toggle_active_page(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(PageToggleActiveMutation::NAME)
                ->args(
                    [
                        'id' => $this->page->id
                    ]
                )
                ->select(
                    [
                        'id',
                        'active'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        PageToggleActiveMutation::NAME => [
                            'id',
                            'active'
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        PageToggleActiveMutation::NAME => [
                            'id' => $this->page->id,
                            'active' => false,
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_off_page_which_is_used_in_menu(): void
    {
        $page = Menu::factory()
            ->create()->page;

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(PageToggleActiveMutation::NAME)
                ->args(
                    [
                        'id' => $page->id
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
                    'errors' => [
                        [
                            'message' => trans('validation.custom.about.page.cant_disable')
                        ]
                    ]
                ]
            );
    }

    public function test_off_page_which_is_used_in_off_menu(): void
    {
        $page = Menu::factory(['active' => false])
            ->create()->page;

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(PageToggleActiveMutation::NAME)
                ->args(
                    [
                        'id' => $page->id
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
                        PageToggleActiveMutation::NAME => [
                            'id' => $page->id
                        ]
                    ]
                ]
            );
    }
}
