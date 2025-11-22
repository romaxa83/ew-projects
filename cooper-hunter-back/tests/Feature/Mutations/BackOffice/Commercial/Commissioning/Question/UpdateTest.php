<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Commissioning\Question;

use App\Enums\Commercial\Commissioning\AnswerPhotoType;
use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Enums\Commercial\Commissioning\AnswerType;
use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Enums\Commercial\Commissioning\ProtocolType;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Question\UpdateMutation;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\Question;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolQuestionBuilder;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = UpdateMutation::NAME;

    protected QuestionBuilder $questionBuilder;
    protected ProjectBuilder $projectBuilder;
    protected ProtocolBuilder $protocolBuilder;
    protected ProjectProtocolBuilder $protocolProjectBuilder;
    protected ProjectProtocolQuestionBuilder $protocolProjectQuestionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->questionBuilder = resolve(QuestionBuilder::class);
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
        $this->protocolProjectBuilder = resolve(ProjectProtocolBuilder::class);
        $this->protocolProjectQuestionBuilder = resolve(ProjectProtocolQuestionBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Question */
        $model = $this->questionBuilder->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $model->id;
        $data['answer_type'] = AnswerType::CHECKBOX;
        $data['photo_type'] = AnswerPhotoType::NOT_NECESSARY;

        $this->assertNotEquals(
            $model->translations()->where('language', 'en')->first()->text,
            data_get($data, 'translations.en.text')
        );
        $this->assertNotEquals(
            $model->translations()->where('language', 'es')->first()->text,
            data_get($data, 'translations.es.text')
        );

        $this->assertTrue($model->status->isDraft());
        $this->assertNotEquals($model->answer_type, data_get($data, 'answer_type'));
        $this->assertNotEquals($model->photo_type, data_get($data, 'photo_type'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $model->id,
                            'answer_type' => data_get($data, 'answer_type'),
                            'photo_type' => data_get($data, 'photo_type'),
                            'status' => data_get($data, 'status'),
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
    public function not_update_if_send_status_active(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Question */
        $model = $this->questionBuilder->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $model->id;
        $data['answer_type'] = AnswerType::CHECKBOX;
        $data['photo_type'] = AnswerPhotoType::NOT_NECESSARY;
        $data['status'] = QuestionStatus::ACTIVE();

        $this->assertNotEquals(
            $model->translations()->where('language', 'en')->first()->text,
            data_get($data, 'translations.en.text')
        );
        $this->assertNotEquals(
            $model->translations()->where('language', 'es')->first()->text,
            data_get($data, 'translations.es.text')
        );

        $this->assertTrue($model->status->isDraft());
        $this->assertNotEquals($model->answer_type, data_get($data, 'answer_type'));
        $this->assertNotEquals($model->photo_type, data_get($data, 'photo_type'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $model->id,
                            'answer_type' => $model->answer_type,
                            'photo_type' => $model->photo_type,
                            'status' => data_get($data, 'status'),
                            'translations' => [
                                [
                                    'text' =>  $model->translations()->where('language', 'en')->first()->text,
                                ],
                                [
                                    'text' =>  $model->translations()->where('language', 'es')->first()->text,
                                ]
                            ]
                        ],
                    ]
                ]
            );

        $model->refresh();

        $this->assertTrue($model->status->isActive());
    }

    /** @test */
    public function not_update_if_send_status_inactive(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Question */
        $model = $this->questionBuilder->setData([
            'status' => QuestionStatus::ACTIVE()
        ])->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $model->id;
        $data['answer_type'] = AnswerType::CHECKBOX;
        $data['photo_type'] = AnswerPhotoType::NOT_NECESSARY;
        $data['status'] = QuestionStatus::INACTIVE();

        $this->assertNotEquals(
            $model->translations()->where('language', 'en')->first()->text,
            data_get($data, 'translations.en.text')
        );
        $this->assertNotEquals(
            $model->translations()->where('language', 'es')->first()->text,
            data_get($data, 'translations.es.text')
        );

        $this->assertTrue($model->status->isActive());
        $this->assertNotEquals($model->answer_type, data_get($data, 'answer_type'));
        $this->assertNotEquals($model->photo_type, data_get($data, 'photo_type'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $model->id,
                            'answer_type' => $model->answer_type,
                            'photo_type' => $model->photo_type,
                            'status' => data_get($data, 'status'),
                            'translations' => [
                                [
                                    'text' =>  $model->translations()->where('language', 'en')->first()->text,
                                ],
                                [
                                    'text' =>  $model->translations()->where('language', 'es')->first()->text,
                                ]
                            ]
                        ],
                    ]
                ]
            );

        $model->refresh();

        $this->assertTrue($model->status->isInactive());
    }

    /** @test */
    public function attach_to_project_protocols_if_status_active(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project_1 CommercialProject */
        $project_1 = $this->projectBuilder->setStartPreCommissioningDate($date)->create();
        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setStatus(QuestionStatus::ACTIVE())->setProtocol($protocol_1)->create();
        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project_1)->create();
        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();

        /** @var $project_2 CommercialProject */
        $project_2 = $this->projectBuilder->setStartPreCommissioningDate($date)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setStatus(QuestionStatus::ACTIVE())->setProtocol($protocol_1)->create();
        $projectProtocol_2 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project_2)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_2)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_2)->create();

        $this->assertEquals(1, $project_1->projectProtocols[0]->projectQuestions->count());
        $this->assertEquals(1, $project_2->projectProtocols[0]->projectQuestions->count());

        /** @var $model Question */
        $question_new = $this->questionBuilder->setProtocol($protocol_1)
            ->setStatus(QuestionStatus::DRAFT())->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $question_new->id;
        $data['status'] = QuestionStatus::ACTIVE();

        $this->assertTrue($question_new->status->isDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => data_get($data, 'status'),
                    ],
                ]
            ]);

        $project_1->refresh();
        $project_2->refresh();

        $this->assertEquals(2, $project_1->projectProtocols[0]->projectQuestions->count());
        $this->assertEquals($project_1->projectProtocols[0]->projectQuestions[1]->question_id, $question_1->id);
        $this->assertEquals($project_1->projectProtocols[0]->projectQuestions[0]->question_id, $question_new->id);

        $this->assertEquals(2, $project_2->projectProtocols[0]->projectQuestions->count());
        $this->assertEquals($project_2->projectProtocols[0]->projectQuestions[1]->question_id, $question_2->id);
        $this->assertEquals($project_2->projectProtocols[0]->projectQuestions[0]->question_id, $question_new->id);
    }

    /** @test */
    public function not_attach_to_project_protocols_if_protocol_done(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project_1 CommercialProject */
        $project_1 = $this->projectBuilder->setStartPreCommissioningDate($date)->create();
        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setStatus(QuestionStatus::ACTIVE())->setProtocol($protocol_1)->create();
        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::DONE())->setProject($project_1)->create();
        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();

        $this->assertTrue($project_1->projectProtocols[0]->isDone());
        $this->assertEquals(1, $project_1->projectProtocols[0]->projectQuestions->count());

        /** @var $model Question */
        $question_new = $this->questionBuilder->setProtocol($protocol_1)
            ->setStatus(QuestionStatus::DRAFT())->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $question_new->id;
        $data['status'] = QuestionStatus::ACTIVE();

        $this->assertTrue($question_new->status->isDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => data_get($data, 'status'),
                    ],
                ]
            ]);

        $project_1->refresh();

        $this->assertEquals(1, $project_1->projectProtocols[0]->projectQuestions->count());
    }

    /** @test */
    public function not_attach_to_project_protocols_if_question_attach_yet(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project_1 CommercialProject */
        $project_1 = $this->projectBuilder->setStartPreCommissioningDate($date)->create();
        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setStatus(QuestionStatus::ACTIVE())->setProtocol($protocol_1)->create();
        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING())->setProject($project_1)->create();
        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();

        $this->assertEquals(1, $project_1->projectProtocols[0]->projectQuestions->count());

        /** @var $model Question */
        $question_new = $this->questionBuilder->setProtocol($protocol_1)
            ->setStatus(QuestionStatus::DRAFT())->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $question_1->id;
        $data['status'] = QuestionStatus::ACTIVE();

        $this->assertTrue($question_new->status->isDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => data_get($data, 'status'),
                    ],
                ]
            ]);

        $project_1->refresh();

        $this->assertEquals(1, $project_1->projectProtocols[0]->projectQuestions->count());
    }

    /** @test */
    public function detach_question_if_status_inactive(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project_1 CommercialProject */
        $project_1 = $this->projectBuilder->setStartPreCommissioningDate($date)->create();
        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setStatus(QuestionStatus::ACTIVE())->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setStatus(QuestionStatus::ACTIVE())->setProtocol($protocol_1)->create();
        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project_1)->create();
        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_2)->create();

        /** @var $project_2 CommercialProject */
        $project_2 = $this->projectBuilder->setStartPreCommissioningDate($date)->create();
        $projectProtocol_2 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project_2)->create();
        $projectProtocolQuestion_3 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_2)
            ->setAnswerStatus(AnswerStatus::REJECT)->setQuestion($question_1)->create();
        $projectProtocolQuestion_4 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_2)
            ->setAnswerStatus(AnswerStatus::ACCEPT)->setQuestion($question_2)->create();

        $this->assertEquals(2, $project_1->projectProtocols[0]->projectQuestions->count());
        $this->assertEquals(2, $project_2->projectProtocols[0]->projectQuestions->count());

        $data = $this->data();
        $data['id'] = $question_2->id;
        $data['status'] = QuestionStatus::INACTIVE();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => data_get($data, 'status'),
                    ],
                ]
            ]);

        $project_1->refresh();
        $project_2->refresh();

        $this->assertEquals(1, $project_1->projectProtocols[0]->projectQuestions->count());
        $this->assertEquals(2, $project_2->projectProtocols[0]->projectQuestions->count());
    }

    /** @test */
    public function fail_switch_status_from_draft_to_inactive(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Question */
        $model = $this->questionBuilder->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $model->id;
        $data['status'] = QuestionStatus::INACTIVE();

        $this->assertTrue($model->status->isDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.can\'t toggle status',[
                        'from_status' => $model->status,
                        'to_status' => $data['status'],
                    ])]
                ]
            ]);
    }

    /** @test */
    public function fail_switch_status_from_active_to_draft(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Question */
        $model = $this->questionBuilder->setData([
            'status' => QuestionStatus::ACTIVE()
        ])->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $model->id;

        $this->assertTrue($model->status->isActive());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.can\'t toggle status',[
                        'from_status' => $model->status,
                        'to_status' => $data['status'],
                    ])]
                ]
            ]);
    }

    /** @test */
    public function fail_switch_status_from_in_active_to_active(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Question */
        $model = $this->questionBuilder->setData([
            'status' => QuestionStatus::INACTIVE()
        ])->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $model->id;
        $data['status'] = QuestionStatus::ACTIVE();

        $this->assertTrue($model->status->isInactive());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.can\'t toggle status',[
                        'from_status' => $model->status,
                        'to_status' => $data['status'],
                    ])]
                ]
            ]);
    }

    /** @test */
    public function fail_switch_status_from_in_active_to_draft(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Question */
        $model = $this->questionBuilder->setData([
            'status' => QuestionStatus::INACTIVE()
        ])->withTranslation()->create();

        $data = $this->data();
        $data['id'] = $model->id;

        $this->assertTrue($model->status->isInactive());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.can\'t toggle status',[
                        'from_status' => $model->status,
                        'to_status' => $data['status'],
                    ])]
                ]
            ]);
    }

    public function data(): array
    {
        return [
            'answer_type' => AnswerType::TEXT,
            'photo_type' => AnswerPhotoType::REQUIRED,
            'status' => QuestionStatus::DRAFT,
            'translations' => [
                'en' => [
                    'language' => 'en',
                    'text' => 'some question title en',
                ],
                'es' => [
                    'language' => 'es',
                    'text' => 'some question title es',
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
                        answer_type: %s
                        photo_type: %s
                        status: %s
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
                    answer_type
                    photo_type
                    status
                    translations {
                        text
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'answer_type'),
            data_get($data, 'photo_type'),
            data_get($data, 'status'),
            data_get($data, 'translations.en.language'),
            data_get($data, 'translations.en.text'),
            data_get($data, 'translations.es.language'),
            data_get($data, 'translations.es.text'),
        );
    }
}

