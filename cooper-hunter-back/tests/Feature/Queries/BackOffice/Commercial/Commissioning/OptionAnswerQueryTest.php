<?php

namespace Tests\Feature\Queries\BackOffice\Commercial\Commissioning;

use App\GraphQL\Queries\BackOffice\Commercial\Commissioning\OptionAnswerQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\OptionAnswerBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\TestCase;

class OptionAnswerQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = OptionAnswerQuery::NAME;

    protected $optionAnswerBuilder;
    protected $questionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->questionBuilder = resolve(QuestionBuilder::class);
        $this->optionAnswerBuilder = resolve(OptionAnswerBuilder::class);
    }

    /** @test */
    public function success_paginator(): void
    {
        $this->loginAsSuperAdmin();

        $q_1 = $this->questionBuilder->create();
        $q_2 = $this->questionBuilder->create();

        $this->optionAnswerBuilder->setQuestion($q_1)->create();
        $this->optionAnswerBuilder->setQuestion($q_1)->create();
        $this->optionAnswerBuilder->setQuestion($q_1)->create();
        $this->optionAnswerBuilder->setQuestion($q_2)->create();
        $this->optionAnswerBuilder->setQuestion($q_2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'meta' => [
                                'total' => 5,
                                'per_page' => 15,
                                'current_page' => 1,
                                'from' => 1,
                                'to' => 5,
                                'last_page' => 1,
                                'has_more_pages' => false,
                            ],
                        ],
                    ]
                ]
            )
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                       data {
                            id
                       }
                       meta {
                            total
                            per_page
                            current_page
                            from
                            to
                            last_page
                            has_more_pages
                       }
                }
            }',
            self::MUTATION
        );
    }

    /** @test */
    public function success_filter_by_id(): void
    {
        $this->loginAsSuperAdmin();

        $q_1 = $this->questionBuilder->create();

        $model = $this->optionAnswerBuilder->setQuestion($q_1)->create();
        $this->optionAnswerBuilder->setQuestion($q_1)->create();
        $this->optionAnswerBuilder->setQuestion($q_1)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $model->id]
                        ],
                        'meta' => [
                            'total' => 1,
                        ],
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrById($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                       data {
                            id
                       }
                       meta {
                            total
                       }
                }
            }',
            self::MUTATION,
            $id
        );
    }

    /** @test */
    public function success_filter_by_question_id(): void
    {
        $this->loginAsSuperAdmin();

        $q_1 = $this->questionBuilder->create();
        $q_2 = $this->questionBuilder->create();

        $this->optionAnswerBuilder->setQuestion($q_1)->create();
        $this->optionAnswerBuilder->setQuestion($q_1)->create();
        $this->optionAnswerBuilder->setQuestion($q_1)->create();
        $this->optionAnswerBuilder->setQuestion($q_2)->create();
        $this->optionAnswerBuilder->setQuestion($q_2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByQuestionId($q_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'meta' => [
                            'total' => 3,
                        ],
                    ],
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::MUTATION.'.data')
        ;
    }

    protected function getQueryStrByQuestionId($id): string
    {
        return sprintf(
            '
            {
                %s (question_id: %s) {
                       data {
                            id
                       }
                       meta {
                            total
                       }
                }
            }',
            self::MUTATION,
            $id
        );
    }
}


