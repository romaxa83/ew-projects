<?php

namespace Tests\Feature\Queries\BackOffice\Faq;

use App\Enums\Faq\Questions\QuestionStatusEnum;
use App\GraphQL\Queries\BackOffice\Faq\QuestionQuery;
use App\Models\Admins\Admin;
use App\Models\Faq\Question;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuestionQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = QuestionQuery::NAME;

    public function test_get_list(): void
    {
        $this->loginAsSuperAdmin();

        Question::factory()
            ->times(5)
            ->create();

        $query = $this->getQuery();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(5, 'data.'. self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getStructure()
            );
    }

    protected function getQuery(array $args = []): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->args($args)
            ->select(
                [
                    'data' => $this->getSelect()
                ]
            )->make();
    }

    protected function getSelect(): array
    {
        return [
            'id',
            'admin' => [
                'id'
            ],
            'status',
            'name',
            'email',
            'question',
            'answer',
        ];
    }

    protected function getStructure(): array
    {
        return [
            'data' => [
                self::QUERY => [
                    'data' => [
                        $this->getSelect()
                    ]
                ],
            ],
        ];
    }

    public function test_filter_by_status(): void
    {
        $this->loginAsSuperAdmin();

        Question::factory()
            ->times(5)
            ->create();

        Question::factory()
            ->times(5)
            ->answered()
            ->create();

        $query = $this->getQuery(
            [
                'status' => new EnumValue(
                    QuestionStatusEnum::ANSWERED
                )
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(5, 'data.'. self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getStructure()
            );
    }

    public function test_filter_by_admin(): void
    {
        $this->loginAsSuperAdmin();

        Question::factory()
            ->times(5)
            ->create();

        Question::factory()
            ->answeredBy($admin = Admin::factory()->create())
            ->create();

        $query = $this->getQuery(
            [
                'admin_id' => $admin->id
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.'. self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getStructure()
            );
    }

    public function test_filter_by_query(): void
    {
        $this->loginAsSuperAdmin();

        $question = Question::factory()
            ->times(5)
            ->create()
            ->first();

        $query = $this->getQuery(
            [
                'query' => $question->name
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.'. self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getStructure()
            );
    }
}
