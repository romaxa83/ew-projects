<?php

namespace Feature\Mutations\BackOffice\Menu;

use App\Enums\Menu\MenuBlockEnum;
use App\Enums\Menu\MenuPositionEnum;
use App\GraphQL\Mutations\BackOffice\Menu\MenuCreateMutation;
use App\Models\About\Page;
use App\Models\About\PageTranslation;
use App\Models\Menu\Menu;
use App\Models\Menu\MenuTranslation;
use App\Permissions\Menu\MenuCreatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;
use Tests\Traits\TranslationHelper;

class MenuCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use TranslationHelper;
    use WithFaker;

    private Page $page;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([MenuCreatePermission::KEY]);

        $this->page = Page::factory()
            ->create();
    }

    public function test_create_menu(): void
    {
        $result = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(MenuCreateMutation::NAME)
                ->args(
                    [
                        'menu' => [
                            'position' => MenuPositionEnum::FOOTER(),
                            'page_id' => $this->page->id,
                            'block' => MenuBlockEnum::OTHER(),
                            'translations' => $this->getTranslationsArray(['title'])
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'position',
                        'page' => [
                            'id',
                            'active',
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
                            'created_at',
                            'updated_at',
                        ],
                        'block',
                        'active',
                        'translation' => [
                            'id',
                            'language',
                            'title',
                        ],
                        'translations' => [
                            'id',
                            'language',
                            'title',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        MenuCreateMutation::NAME => [
                            'id',
                            'active',
                            'page' => [
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
                                ],
                            ],
                            'block',
                            'position',
                            'translation' => [
                                'id',
                                'language',
                                'title',
                            ],
                            'translations' => [
                                '*' => [
                                    'id',
                                    'language',
                                    'title',
                                ]
                            ]
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Menu::class,
            [
                'page_id' => $this->page->id
            ]
        );

        $menu = Menu::wherePageId($this->page->id)
            ->first();

        $result->assertJson(
            [
                'data' => [
                    MenuCreateMutation::NAME => [
                        'id' => $menu->id,
                        'active' => true,
                        'page' => [
                            'id' => $this->page->id,
                            'active' => true,
                            'created_at' => $this->page->created_at->getTimestamp(),
                            'updated_at' => $this->page->updated_at->getTimestamp(),
                            'translation' => [
                                'id' => $this->page->translation->id,
                                'title' => $this->page->translation->title,
                                'description' => $this->page->translation->description,
                                'language' => $this->page->translation->language,
                            ],
                            'translations' => $this->page->translations
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
                        ],
                        'block' => MenuBlockEnum::OTHER,
                        'position' => MenuPositionEnum::FOOTER,
                        'translation' => [
                            'id' => $menu->translation->id,
                            'language' => $menu->translation->language,
                            'title' => $menu->translation->title,
                        ],
                        'translations' => $menu->translations
                            ->map(
                                fn(MenuTranslation $menuTranslation) => [
                                    'id' => $menuTranslation->id,
                                    'title' => $menuTranslation->title,
                                    'language' => $menuTranslation->language
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
