<?php

namespace Feature\Queries\BackOffice\About;

use App\GraphQL\Queries\BackOffice\About\PageQuery;
use App\Models\About\Page;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class PageQueryTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    /**@var <Page>[] $pages */
    private array $pages;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdmin();

        $this->pages = array_merge(
            Page::factory(
                [
                    'active' => false,
                ]
            )
                ->count(2)
                ->create()
                ->toArray(),
            Page::factory()
                ->count(3)
                ->create()
                ->toArray()
        );
    }

    public function test_get_all_pages(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(PageQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        PageQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        PageQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->pages[4]['id']
                                ],
                                [
                                    'id' => $this->pages[3]['id']
                                ],
                                [
                                    'id' => $this->pages[2]['id']
                                ],
                                [
                                    'id' => $this->pages[1]['id']
                                ],
                                [
                                    'id' => $this->pages[0]['id']
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(5, 'data.' . PageQuery::NAME . '.data');
    }

    public function test_get_active_pages(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(PageQuery::NAME)
                ->args(
                    [
                        'published' => true
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        PageQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        PageQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->pages[4]['id']
                                ],
                                [
                                    'id' => $this->pages[3]['id']
                                ],
                                [
                                    'id' => $this->pages[2]['id']
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . PageQuery::NAME . '.data');
    }

    public function test_get_page_by_id(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(PageQuery::NAME)
                ->args(
                    [
                        'id' => $this->pages[0]['id']
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        PageQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        PageQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->pages[0]['id']
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . PageQuery::NAME . '.data');
    }

    public function test_get_page_by_title(): void
    {
        $page = Page::find($this->pages[3]['id']);

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(PageQuery::NAME)
                ->args(
                    [
                        'query' => $page->translation->title
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        PageQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJson(
                [
                    'data' => [
                        PageQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->pages[3]['id']
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . PageQuery::NAME . '.data');
    }
}
