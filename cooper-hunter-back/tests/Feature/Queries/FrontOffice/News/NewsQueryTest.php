<?php

namespace Tests\Feature\Queries\FrontOffice\News;

use App\GraphQL\Queries\FrontOffice\News\NewsQuery;
use App\Models\News\News;
use App\Models\News\NewsTranslation;
use App\Models\News\Tag;
use App\Models\News\TagTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NewsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = NewsQuery::NAME;

    public function test_success_list(): void
    {
        News::factory()
            ->times(5)
            ->for(
                Tag::factory()
                    ->has(
                        TagTranslation::factory()->enLocale(),
                        'translations'
                    )
            )
            ->has(NewsTranslation::factory()->enLocale(), 'translations')
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            select: [
                'data' => [
                    'id',
                    'translation' => [
                        'title',
                        'description',
                    ],
                    'tag' => [
                        'color',
                        'translation' => [
                            'title',
                        ],
                    ],
                ],
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertJsonCount(5, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id',
                                    'translation' => [
                                        'title',
                                        'description',
                                    ],
                                    'tag' => [
                                        'color',
                                        'translation' => [
                                            'title',
                                        ],
                                    ],
                                ]
                            ],
                        ]
                    ],
                ]
            );
    }

    public function test_filter_by_tag(): void
    {
        News::factory()
            ->times(5)
            ->for(Tag::factory()->create())
            ->create();

        News::factory()
            ->times(5)
            ->for($tag = Tag::factory()->create())
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'tag_id' => $tag->id,
            ],
            [
                'data' => [
                    'id',
                ],
            ]
        );

        $this->assertCanViewNews($query, 5);
    }

    protected function assertCanViewNews(GraphQLQuery $query, int $count): void
    {
        $this->postGraphQL($query->getQuery())
            ->assertJsonCount($count, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id',
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_filter_by_all_tags(): void
    {
        News::factory()
            ->times(5)
            ->for(Tag::factory()->create())
            ->create();

        News::factory()
            ->times(5)
            ->for(Tag::factory()->create())
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [],
            [
                'data' => [
                    'id',
                ],
            ]
        );

        $this->assertCanViewNews($query, 10);
    }

    public function test_filter_by_search_query(): void
    {
        News::factory()
            ->times(5)
            ->for(Tag::factory()->create())
            ->has(NewsTranslation::factory()->allLocales(), 'translations')
            ->create();

        $news = News::factory()
            ->times(5)
            ->for(Tag::factory()->create())
            ->has(NewsTranslation::factory()->allLocales(), 'translations')
            ->create()
            ->first();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'query' => $news->translation->title
            ],
            [
                'data' => [
                    'id',
                ],
            ]
        );

        $this->assertCanViewNews($query, 1);
    }

    public function test_filter_by_id(): void
    {
        $news = News::factory()
            ->times(5)
            ->has(NewsTranslation::factory()->allLocales(), 'translations')
            ->create()
            ->first();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'ids' => [$news->id]
            ],
            [
                'data' => [
                    'id',
                    'prevId',
                    'nextId',
                ],
            ]
        );

        $this->assertCanViewNews($query, 1);
    }
}
