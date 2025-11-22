<?php

namespace Tests\Feature\Mutations\BackOffice\Faq\Questions;

use App\GraphQL\Mutations\BackOffice\Faq\Questions\AnswerQuestionMutation;
use App\Models\Faq\Question;
use App\Notifications\Faq\Questions\AnswerTheQuestionNotification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class AnswerQuestionMutationTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotifications;
    use WithFaker;

    public const MUTATION = AnswerQuestionMutation::NAME;

    public function test_answer_the_question(): void
    {
        Notification::fake();

        $this->loginAsSuperAdmin();

        $question = Question::factory()->create();

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'question_id' => $question->id,
                    'input' => [
                        'answer' => $this->faker->paragraph,
                    ]
                ]
            )
            ->select(
                $structure = [
                    'id',
                    'admin' => [
                        'id'
                    ],
                    'status',
                    'name',
                    'email',
                    'question',
                    'answer',
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => $structure,
                    ],
                ]
            );

        $this->assertNotificationSentTo(
            $question->getEmailString(),
            AnswerTheQuestionNotification::class
        );
    }
}
