<?php

namespace Tests\Feature\Api\QuestionAnswer;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class DeleteQuestionAnswerTest extends TestCase
{
    use DatabaseTransactions;

    private $question_answer_fields_required = [
        'question_en' => 'How can order delivery?',
        'question_ru' => 'How can order delivery?',
        'question_es' => 'How can order delivery?',
        'answer_en' => 'Dear Sir or Madam, you can order delivery using our system.',
        'answer_ru' => 'Dear Sir or Madam, you can order delivery using our system.',
        'answer_es' => 'Dear Sir or Madam, you can order delivery using our system.',
    ];

    public function test_it_question_answer_deleted()
    {
        $this->loginAsCarrierAdmin();

        // create QuestionAnswer
        $response = $this->postJson(
            route('question-answer.store'),
            $this->question_answer_fields_required
        )
            ->assertCreated();

        // get created QuestionAnswer data
        $questionAnswer = $response->getData(true)['data'];

        // check if exists in database
        $this->assertDatabaseHas(
            'questions_answers',
            [
                'id' => $questionAnswer['id']
            ]
        );

        // delete QuestionAnswer
        $response = $this->deleteJson(route('question-answer.destroy', $questionAnswer['id']));

        // check if deleted
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        // check if missing in db
        $this->assertDatabaseMissing(
            'questions_answers',
            [
                'id' => $questionAnswer['id']
            ]
        );
    }
}
