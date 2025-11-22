<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer;

use App\Enums\Commercial\Commissioning\AnswerType;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer\CreateMutation;
use App\Models\Commercial\Commissioning\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CreateMutation::NAME;

    protected $questionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->questionBuilder = resolve(QuestionBuilder::class);
    }

    /** @test */
    public function success_create_question_type_checkbox(): void
    {
        $this->loginAsSuperAdmin();
        /** @var $question Question */
        $question = $this->questionBuilder
            ->setAnswerType(AnswerType::CHECKBOX)->create();

        $data = $this->data();
        $data['question_id'] = $question->id;

        $this->assertTrue($question->isAnswerCheckbox());
        $this->assertEmpty($question->optionAnswers);

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'translations' => [
                            [
                                'text' => data_get($data, 'translations.en.text'),
                            ],
                            [
                                'text' => data_get($data, 'translations.es.text'),
                            ]
                        ]
                    ],
                ]
            ]);

        $id = $res->json('data.'.self::MUTATION.'.id');

        $question->refresh();

        $this->assertNotEmpty($question->optionAnswers);
        $this->assertEquals($question->optionAnswers->first()->id, $id);
    }

    /** @test */
    public function success_create_question_type_radio(): void
    {
        $this->loginAsSuperAdmin();
        /** @var $question Question */
        $question = $this->questionBuilder
            ->setAnswerType(AnswerType::RADIO)->create();

        $data = $this->data();
        $data['question_id'] = $question->id;

        $this->assertTrue($question->isAnswerRadio());
        $this->assertEmpty($question->optionAnswers);

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'translations' => [
                            [
                                'text' => data_get($data, 'translations.en.text'),
                            ],
                            [
                                'text' => data_get($data, 'translations.es.text'),
                            ]
                        ]
                    ],
                ]
            ]);

        $id = $res->json('data.'.self::MUTATION.'.id');

        $question->refresh();

        $this->assertNotEmpty($question->optionAnswers);
        $this->assertEquals($question->optionAnswers->first()->id, $id);
    }

    /** @test */
    public function fail_create_question_type_text(): void
    {
        $this->loginAsSuperAdmin();
        /** @var $question Question */
        $question = $this->questionBuilder
            ->setAnswerType(AnswerType::TEXT)->create();

        $data = $this->data();
        $data['question_id'] = $question->id;

        $this->assertTrue($question->isAnswerText());
        $this->assertEmpty($question->optionAnswers);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.not create option answer by question')]
                ]
            ]);
    }

    /** @test */
    public function fail_create_because_question_is_not_draft(): void
    {
        $this->loginAsSuperAdmin();
        /** @var $question Question */
        $question = $this->questionBuilder
            ->setData(['status' => QuestionStatus::ACTIVE])
            ->setAnswerType(AnswerType::RADIO())->create();

        $data = $this->data();
        $data['question_id'] = $question->id;

        $this->assertEmpty($question->optionAnswers);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.can\'t create an option answer')]
                ]
            ]);
    }

    public function data(): array
    {
        return [
            'translations' => [
                'en' => [
                    'language' => 'en',
                    'text' => 'some option answer en',
                ],
                'es' => [
                    'language' => 'es',
                    'text' => 'some option answer es',
                ],
            ]
        ];
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    input: {
                        question_id: %s
                        translations: [
                            {
                                language: %s,
                                text: "%s"
                            },
                            {
                                language: %s,
                                text: "%s",
                            },
                        ]
                    },
                ) {
                    id
                    translations {
                        text
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'question_id'),
            data_get($data, 'translations.en.language'),
            data_get($data, 'translations.en.text'),
            data_get($data, 'translations.es.language'),
            data_get($data, 'translations.es.text'),
        );
    }
}
