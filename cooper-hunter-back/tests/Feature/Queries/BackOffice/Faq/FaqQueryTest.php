<?php

namespace Tests\Feature\Queries\BackOffice\Faq;

use App\GraphQL\Queries\BackOffice\Faq\FaqQuery;
use App\Models\Faq\Faq;
use App\Models\Faq\FaqTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FaqQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = FaqQuery::NAME;

    public function test_faq(): void
    {
        $this->loginAsAdmin();

        Faq::factory()
            ->times(10)
            ->has(FaqTranslation::factory()->allLocales(), 'translations')
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            select: [
                'translation' => [
                    'question',
                    'answer',
                ],
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(10, 'data.' . self::QUERY)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'translation' => [
                                    'question',
                                    'answer',
                                ]
                            ]
                        ],
                    ],
                ]
            );
    }

    public function test_filter_by_active_faq(): void
    {
        $this->loginAsAdmin();

        Faq::factory()
            ->times(10)
            ->sequence(
                [
                    'active' => true,
                ],
                [
                    'active' => false,
                ]
            )
            ->has(FaqTranslation::factory()->allLocales(), 'translations')
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            ['active' => true], ['id']
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertJsonCount(5, 'data.' . self::QUERY);

        $query = new GraphQLQuery(
            self::QUERY,
            ['active' => false], ['id']
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertJsonCount(5, 'data.' . self::QUERY);
    }
}
