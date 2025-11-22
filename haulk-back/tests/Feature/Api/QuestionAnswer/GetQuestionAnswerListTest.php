<?php

namespace Tests\Feature\Api\QuestionAnswer;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class GetQuestionAnswerListTest extends TestCase
{
    use DatabaseTransactions;

    public function testIfNotAuthorized()
    {
        $response = $this->getJson(route('question-answer.index'));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testIfAuthorizedAllowedToDispatcher()
    {
        $this->loginAsCarrierDispatcher();

        $this->getJson(route('question-answer.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta',]);
    }

    public function testIfAuthorizedAllowedToDriver()
    {
        $this->loginAsCarrierDriver();

        $this->getJson(route('question-answer.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta',]);
    }


    public function testIfAuthorizedAllowedToAdmin()
    {
        $this->loginAsCarrierAdmin();

        $this->getJson(route('question-answer.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta',]);
    }
}
