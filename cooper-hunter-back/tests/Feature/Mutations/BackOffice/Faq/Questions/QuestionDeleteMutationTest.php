<?php

namespace Tests\Feature\Mutations\BackOffice\Faq\Questions;

use App\GraphQL\Mutations\BackOffice\Faq\Questions\QuestionDeleteMutation;
use App\Models\Faq\Question;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class QuestionDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = QuestionDeleteMutation::NAME;

    public function test_delete_unanswered(): void
    {
        $this->loginAsSuperAdmin();

        $question = Question::factory()->create();

        $this->makeQuery($question)
            ->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertModelMissing($question);
    }

    protected function makeQuery(Question $question): TestResponse
    {
        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'question_id' => $question->id,
                ]
            )
            ->make();

        return $this->postGraphQLBackOffice($query);
    }

    public function test_delete_answered(): void
    {
        $this->loginAsSuperAdmin();

        $question = Question::factory()
            ->answered()
            ->create();

        $this->assertServerError($this->makeQuery($question), 'Cannot delete answered Question');

        $this->assertModelExists($question);
    }
}
