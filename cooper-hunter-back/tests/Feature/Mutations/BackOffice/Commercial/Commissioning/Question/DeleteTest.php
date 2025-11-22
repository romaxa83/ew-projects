<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Commissioning\Question;

use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Question\DeleteMutation;
use App\Models\Commercial\Commissioning\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DeleteMutation::NAME;

    protected $questionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->questionBuilder = resolve(QuestionBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $this->loginAsSuperAdmin();

        $model = $this->questionBuilder->create();

        $id = $model->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ]);

        $this->assertNull(Question::find($id));
    }

    /** @test*/
    public function fail_if_active_status(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Question */
        $model = $this->questionBuilder->setData([
            'status' => QuestionStatus::ACTIVE()
        ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.can\'t delete question')]
                ]
            ]);
    }

    /** @test*/
    public function fail_if_in_active_status(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Question */
        $model = $this->questionBuilder->setData([
            'status' => QuestionStatus::INACTIVE()
        ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.can\'t delete question')]
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


