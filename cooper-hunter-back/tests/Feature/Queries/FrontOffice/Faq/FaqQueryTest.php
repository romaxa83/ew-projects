<?php

namespace Tests\Feature\Queries\FrontOffice\Faq;

use App\GraphQL\Queries\FrontOffice\Faq\FaqQuery;
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

        $this->postGraphQL($query->getQuery())
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
}
