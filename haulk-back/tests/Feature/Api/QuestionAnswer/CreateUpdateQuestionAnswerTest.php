<?php

namespace Tests\Feature\Api\QuestionAnswer;

use App\Models\QuestionAnswer\QuestionAnswer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class CreateUpdateQuestionAnswerTest extends TestCase
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

    private $question_answer_fields_update = [
        'question_en' => 'How can create an order ?',
        'question_ru' => 'How can create an order ?',
        'question_es' => 'How can create an order ?',
        'answer_en' => 'Dear Sir or Madam, you can create an order using our system.',
        'answer_ru' => 'Dear Sir or Madam, you can create an order using our system.',
        'answer_es' => 'Dear Sir or Madam, you can create an order using our system.',
    ];

    public function testIfNoRequiredFields()
    {
        $this->loginAsCarrierAdmin();

        $response = $this->postJson(route('question-answer.store'), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing(QuestionAnswer::TABLE_NAME, [
            'question_en' => $this->question_answer_fields_required['question_en'],
            'answer_en' => $this->question_answer_fields_required['answer_en'],
        ]);
    }

    public function testIfQuestionAnswerCreated()
    {
        $this->loginAsCarrierAdmin();

        // create QuestionAnswer
        $response = $this->postJson(route('question-answer.store'), $this->question_answer_fields_required);

        // check if created
        $response->assertStatus(Response::HTTP_CREATED);

        // get created QuestionAnswer data
        $questionAnswer = $response->getData(true)['data'];

        // check if exists in database
        $this->assertDatabaseHas('questions_answers', [
            'id' => $questionAnswer['id']
        ]);
    }

    public function testIfQuestionAnswerCanNotBeCreatedByDispatcherCreated()
    {
        $this->loginAsCarrierDispatcher();

        // create QuestionAnswer
        $response = $this->postJson(route('question-answer.store'), $this->question_answer_fields_required);

        // check if created
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testIfQuestionAnswerUpdated()
    {
        $this->loginAsCarrierAdmin();

        // create QuestionAnswer
        $response = $this->postJson(route('question-answer.store'), $this->question_answer_fields_required);

        // check if created
        $response->assertStatus(Response::HTTP_CREATED);

        // get created QuestionAnswer data
        $questionAnswer = $response->getData(true)['data'];

        // update QuestionAnswer
        $response = $this->putJson(route('question-answer.update', $questionAnswer['id']), $this->question_answer_fields_update);

        // check if updated
        $response->assertOk();

        // check if updated in db
        $this->assertDatabaseHas('questions_answers', [
                'id' => $questionAnswer['id']
            ] + $this->question_answer_fields_update);
    }
}
