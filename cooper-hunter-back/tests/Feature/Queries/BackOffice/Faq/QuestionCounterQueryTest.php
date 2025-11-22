<?php

namespace Tests\Feature\Queries\BackOffice\Faq;

use App\GraphQL\Queries\BackOffice\Faq\QuestionCounterQuery;
use App\Models\Faq\Question;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuestionCounterQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_counter(): void
    {
        $this->loginAsSuperAdmin();

        Question::factory()
            ->times(5)
            ->create();
        Question::factory()
            ->times(10)
            ->answered()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(QuestionCounterQuery::NAME)
                ->select([
                    'without_answer',
                    'with_answer',
                    'total'
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    QuestionCounterQuery::NAME => [
                        'without_answer' => 5,
                        'with_answer' => 10,
                        'total' => 15
                    ]
                ]
            ]);
    }

    public function test_get_counter_without_question(): void
    {
        $this->loginAsSuperAdmin();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(QuestionCounterQuery::NAME)
                ->select([
                    'without_answer',
                    'with_answer',
                    'total'
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    QuestionCounterQuery::NAME => [
                        'without_answer' => 0,
                        'with_answer' => 0,
                        'total' => 0
                    ]
                ]
            ]);
    }
}
