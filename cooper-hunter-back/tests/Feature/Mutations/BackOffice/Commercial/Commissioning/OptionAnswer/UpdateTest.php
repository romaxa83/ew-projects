<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer;

use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer\UpdateMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\OptionAnswerBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = UpdateMutation::NAME;

    protected $optionAnswerBuilder;
    protected $questionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->questionBuilder = resolve(QuestionBuilder::class);
        $this->optionAnswerBuilder = resolve(OptionAnswerBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        $this->loginAsSuperAdmin();

        $question = $this->questionBuilder->create();
        $model = $this->optionAnswerBuilder->setQuestion($question)
            ->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $model->id;

        $this->assertNotEquals(
            $model->translations()->where('language', 'en')->first()->text,
            data_get($data, 'translations.en.text')
        );
        $this->assertNotEquals(
            $model->translations()->where('language', 'es')->first()->text,
            data_get($data, 'translations.es.text')
        );

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $model->id,
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
                ]
            );
    }

    /** @test */
    public function fail_create_because_question_is_not_draft(): void
    {
        $this->loginAsSuperAdmin();

        $question = $this->questionBuilder->setData(['status' => QuestionStatus::INACTIVE])->create();
        $model = $this->optionAnswerBuilder->setQuestion($question)
            ->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $model->id;

        $this->assertNotEquals(
            $model->translations()->where('language', 'en')->first()->text,
            data_get($data, 'translations.en.text')
        );
        $this->assertNotEquals(
            $model->translations()->where('language', 'es')->first()->text,
            data_get($data, 'translations.es.text')
        );

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.can\'t update an option answer')]
                ]
            ]);
    }

    public function data(): array
    {
        return [
            'translations' => [
                'en' => [
                    'language' => 'en',
                    'text' => 'some update title en',
                ],
                'es' => [
                    'language' => 'es',
                    'text' => 'some update title es',
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
                    id: %s
                    input: {
                        translations: [
                            {
                                language: %s,
                                text: "%s",
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
            data_get($data, 'id'),
            data_get($data, 'translations.en.language'),
            data_get($data, 'translations.en.text'),
            data_get($data, 'translations.es.language'),
            data_get($data, 'translations.es.text'),
        );
    }
}

