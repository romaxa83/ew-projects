<?php

namespace Feature\Mutations\BackOffice\About\Pages;

use App\GraphQL\Mutations\BackOffice\About\Pages\PageCreateMutation;
use App\Models\About\Page;
use App\Models\About\PageTranslation;
use App\Permissions\About\Pages\PageCreatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;
use Tests\Traits\TranslationHelper;

class PageCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use TranslationHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([PageCreatePermission::KEY]);
    }

    public function test_create_page(): void
    {
        $result = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(PageCreateMutation::NAME)
                ->args(
                    [
                        'page' => [
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
                        'updated_at',
                        'slug',
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
                        PageCreateMutation::NAME => [
                            'id',
                            'active',
                            'slug',
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

        $page = Page::query()
            ->page()
            ->first();

        $result->assertJson(
            [
                'data' => [
                    PageCreateMutation::NAME => [
                        'id' => $page->id,
                        'active' => $page->active,
                        'slug' => 'slug',
                        'created_at' => $page->created_at->getTimestamp(),
                        'updated_at' => $page->updated_at->getTimestamp(),
                        'translation' => [
                            'id' => $page->translation->id,
                            'title' => $page->translation->title,
                            'description' => $page->translation->description,
                            'language' => $page->translation->language,
                        ],
                        'translations' => $page
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
}
