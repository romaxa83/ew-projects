<?php

namespace Feature\Mutations\BackOffice\About\Pages;

use App\GraphQL\Mutations\BackOffice\About\Pages\PageUpdateMutation;
use App\Models\About\Page;
use App\Models\About\PageTranslation;
use App\Models\Menu\Menu;
use App\Permissions\About\Pages\PageUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;
use Tests\Traits\TranslationHelper;

class PageUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use TranslationHelper;

    private Page $page;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([PageUpdatePermission::KEY]);

        $this->page = Page::factory()
            ->create();
    }

    public function test_update_page(): void
    {
        $result = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(PageUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $this->page->id,
                        'page' => [
                            'active' => false,
                            'slug' => 'slug',
                            'translations' => $this->getTranslationsArray()
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'created_at',
                        'slug',
                        'updated_at',
                        'translation' => [
                            'id',
                            'title',
                            'description',
                            'language',
                        ],
                        'translations' => [
                            'id',
                            'title',
                            'description',
                            'language',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        PageUpdateMutation::NAME => [
                            'id',
                            'slug',
                            'active',
                            'created_at',
                            'updated_at',
                            'translation' => [
                                'id',
                                'title',
                                'description',
                                'language',
                            ],
                            'translations' => [
                                '*' => [
                                    'id',
                                    'title',
                                    'description',
                                    'language',
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->page->refresh();

        $result->assertJson(
            [
                'data' => [
                    PageUpdateMutation::NAME => [
                        'id' => $this->page->id,
                        'active' => false,
                        'slug' => 'slug',
                        'created_at' => $this->page->created_at->getTimestamp(),
                        'updated_at' => $this->page->updated_at->getTimestamp(),
                        'translation' => [
                            'id' => $this->page->translation->id,
                            'title' => $this->page->translation->title,
                            'description' => $this->page->translation->description,
                            'language' => $this->page->translation->language,
                        ],
                        'translations' => $this->page
                            ->translations
                            ->map(
                                fn(PageTranslation $pageTranslation) => [
                                    'id' => $pageTranslation->id,
                                    'title' => $pageTranslation->title,
                                    'description' => $pageTranslation->description,
                                    'language' => $pageTranslation->language
                                ]
                            )
                            ->values()
                            ->toArray()
                    ]
                ]
            ]
        );
    }

    public function test_try_to_off_page_which_used_in_menu(): void
    {
        $page = Menu::factory()
            ->create()->page;

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(PageUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $page->id,
                        'page' => [
                            'active' => false,
                            'slug' => 'slug',
                            'translations' => $this->getTranslationsArray()
                        ]
                    ]
                )
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
                    'errors' => [
                        [
                            'message' => trans('validation.custom.about.page.cant_disable')
                        ]
                    ]
                ]
            );
    }
}
