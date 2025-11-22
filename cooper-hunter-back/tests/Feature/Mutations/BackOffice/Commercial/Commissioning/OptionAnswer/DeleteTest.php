<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer;

use App\Enums\Commercial\Commissioning\AnswerType;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer\DeleteMutation;
use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Models\Commercial\Commissioning\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\OptionAnswerBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DeleteMutation::NAME;

    protected $optionAnswerBuilder;
    protected $questionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->optionAnswerBuilder = resolve(OptionAnswerBuilder::class);
        $this->questionBuilder = resolve(QuestionBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $question Question */
        $question = $this->questionBuilder
            ->setAnswerType(AnswerType::CHECKBOX)->create();
        $model = $this->optionAnswerBuilder->setQuestion($question)->create();

        $id = $model->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ]);

        $this->assertNull(OptionAnswer::find($id));
    }

    /** @test */
    public function fail_create_because_question_is_not_draft(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $question Question */
        $question = $this->questionBuilder
            ->setData(['status' => QuestionStatus::INACTIVE])
            ->setAnswerType(AnswerType::CHECKBOX)->create();
        $model = $this->optionAnswerBuilder->setQuestion($question)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.can\'t delete an option answer')]
                ]
            ]);
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                )
            }',
            self::MUTATION,
            $id
        );
    }
}
