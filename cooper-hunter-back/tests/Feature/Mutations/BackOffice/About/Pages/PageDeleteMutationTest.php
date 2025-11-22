<?php

namespace Feature\Mutations\BackOffice\About\Pages;

use App\GraphQL\Mutations\BackOffice\About\Pages\PageDeleteMutation;
use App\Models\About\Page;
use App\Models\Menu\Menu;
use App\Permissions\About\Pages\PageDeletePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class PageDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    private Page $page;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([PageDeletePermission::KEY]);

        $this->page = Page::factory()
            ->create();
    }

    public function test_toggle_active_page(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(PageDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $this->page->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        PageDeleteMutation::NAME
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        PageDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Page::class,
            [
                'id' => $this->page->id
            ]
        );
    }

    public function test_try_to_delete_page_which_is_used_in_menu(): void
    {
        $page = Menu::factory()
            ->create()->page;

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(PageDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $page->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.about.page.cant_delete')
                        ]
                    ]
                ]
            );
    }
}
