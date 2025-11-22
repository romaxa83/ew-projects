<?php

namespace Tests\Feature\Mutations\FrontOffice\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerPhotoType;
use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Enums\Commercial\Commissioning\AnswerType;
use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Enums\Commercial\Commissioning\ProtocolType;
use App\GraphQL\Mutations\FrontOffice\Commercial\Commissioning\AnswersCreateOrUpdateMutation;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\Answer;
use App\Models\Media\Media;
use App\Models\Technicians\Technician;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Commercial\Commissioning\AnswerBuilder;
use Tests\Builders\Commercial\Commissioning\OptionAnswerBuilder;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolQuestionBuilder;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class AnswersCreateOrUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = AnswersCreateOrUpdateMutation::NAME;

    protected $protocolBuilder;
    protected $protocolProjectBuilder;
    protected $protocolProjectQuestionBuilder;
    protected $projectBuilder;
    protected $optionAnswerBuilder;
    protected $questionBuilder;
    protected $answerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->questionBuilder = resolve(QuestionBuilder::class);
        $this->optionAnswerBuilder = resolve(OptionAnswerBuilder::class);
        $this->protocolProjectBuilder = resolve(ProjectProtocolBuilder::class);
        $this->protocolProjectQuestionBuilder = resolve(ProjectProtocolQuestionBuilder::class);
        $this->answerBuilder = resolve(AnswerBuilder::class);
    }

    /** @test */
    public function success_create_some_fields(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::RADIO)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();
        $question_3 = $this->questionBuilder->setAnswerType(AnswerType::CHECKBOX)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();

        $option_answer_1 = $this->optionAnswerBuilder->setQuestion($question_3)->create();
        $option_answer_2 = $this->optionAnswerBuilder->setQuestion($question_3)->create();
        $option_answer_3 = $this->optionAnswerBuilder->setQuestion($question_3)->create();

        $option_answer_4 = $this->optionAnswerBuilder->setQuestion($question_2)->create();
        $option_answer_5 = $this->optionAnswerBuilder->setQuestion($question_2)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_2)->create();
        $projectProtocolQuestion_3 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_3)->create();

        $this->assertNull($projectProtocolQuestion_1->answer);
        $this->assertNull($projectProtocolQuestion_2->answer);
        $this->assertNull($projectProtocolQuestion_3->answer);
        $this->assertEquals($projectProtocol_1->status, ProtocolStatus::DRAFT);

        $this->assertEquals($projectProtocolQuestion_1->answer_status, AnswerStatus::NONE);
        $this->assertEquals($projectProtocolQuestion_2->answer_status, AnswerStatus::NONE);
        $this->assertEquals($projectProtocolQuestion_3->answer_status, AnswerStatus::NONE);

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text 1'
            ],
            [
                'project_protocol_question_id' => $projectProtocolQuestion_2->id,
                'option_answer_ids' => [
                    $option_answer_5->id,
                ]
            ],
            [
                'project_protocol_question_id' => $projectProtocolQuestion_3->id,
                'option_answer_ids' => [
                    $option_answer_1->id,
                    $option_answer_3->id,
                ]
            ]
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrSomeFields($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $projectProtocol_1->id,
                        'status' => ProtocolStatus::PENDING,
                        'project_questions' => [
                            [
                                'id' => $projectProtocolQuestion_3->id,
                                'answer_status' => AnswerStatus::DRAFT,
                                'answer' => [
                                    'text' => null,
                                    'option_answers' => [
                                        ['id' => $option_answer_1->id],
                                        ['id' => $option_answer_3->id],
                                    ],
                                ]
                            ],
                            [
                                'id' => $projectProtocolQuestion_2->id,
                                'answer_status' => AnswerStatus::DRAFT,
                                'answer' => [
                                    'text' => null,
                                    'option_answers' => [
                                        ['id' => $option_answer_5->id],
                                    ],
                                ]
                            ],
                            [
                                'id' => $projectProtocolQuestion_1->id,
                                'answer_status' => AnswerStatus::DRAFT,
                                'answer' => [
                                    'text' => data_get($data, '0.text'),
                                    'option_answers' => [],
                                ]
                            ],
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(0, 'data.'. self::MUTATION . '.project_questions.2.answer.option_answers')
            ->assertJsonCount(1, 'data.'. self::MUTATION . '.project_questions.1.answer.option_answers')
            ->assertJsonCount(2, 'data.'. self::MUTATION . '.project_questions.0.answer.option_answers')
        ;
    }

    /** @test */
    public function success_update_some_fields(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::RADIO)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();
        $question_3 = $this->questionBuilder->setAnswerType(AnswerType::CHECKBOX)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();

        $option_answer_1 = $this->optionAnswerBuilder->setQuestion($question_3)->create();
        $option_answer_2 = $this->optionAnswerBuilder->setQuestion($question_3)->create();
        $option_answer_3 = $this->optionAnswerBuilder->setQuestion($question_3)->create();

        $option_answer_4 = $this->optionAnswerBuilder->setQuestion($question_2)->create();
        $option_answer_5 = $this->optionAnswerBuilder->setQuestion($question_2)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->setAnswerStatus(AnswerStatus::DRAFT)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_2)->create();
        $projectProtocolQuestion_3 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_3)->setAnswerStatus(AnswerStatus::DRAFT)->create();

        $answer_1 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)
            ->setText('first text')->create();
        $answer_2 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_3)
            ->setOptionAnswers($option_answer_3, $option_answer_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text 1'
            ],
            [
                'project_protocol_question_id' => $projectProtocolQuestion_2->id,
                'option_answer_ids' => [
                    $option_answer_5->id,
                ]
            ],
            [
                'project_protocol_question_id' => $projectProtocolQuestion_3->id,
                'option_answer_ids' => [
                    $option_answer_2->id,
                ]
            ]
        ];

        $this->assertNotEquals($projectProtocolQuestion_1->answer->text, data_get($data, '0.text'));
        $this->assertNull($projectProtocolQuestion_2->answer);
        $this->assertCount(2, $projectProtocolQuestion_3->answer->optionAnswers);

        $this->assertEquals($projectProtocol_1->status, ProtocolStatus::PENDING);

        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());
        $this->assertTrue($projectProtocolQuestion_2->answerIsNone());
        $this->assertTrue($projectProtocolQuestion_3->answerIsDraft());

        $this->postGraphQL([
            'query' => $this->getQueryStrSomeFields($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $projectProtocol_1->id,
                        'status' => ProtocolStatus::PENDING,
                        'project_questions' => [
                            [
                                'id' => $projectProtocolQuestion_3->id,
                                'answer_status' => AnswerStatus::DRAFT,
                                'answer' => [
                                    'text' => null,
                                    'option_answers' => [
                                        ['id' => $option_answer_2->id],
                                    ],
                                ]
                            ],
                            [
                                'id' => $projectProtocolQuestion_2->id,
                                'answer_status' => AnswerStatus::DRAFT,
                                'answer' => [
                                    'text' => null,
                                    'option_answers' => [
                                        ['id' => $option_answer_5->id]
                                    ],
                                ]
                            ],
                            [
                                'id' => $projectProtocolQuestion_1->id,
                                'answer_status' => AnswerStatus::DRAFT,
                                'answer' => [
                                    'text' => data_get($data, '0.text'),
                                    'option_answers' => [],
                                ]
                            ],
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(0, 'data.'. self::MUTATION . '.project_questions.2.answer.option_answers')
            ->assertJsonCount(1, 'data.'. self::MUTATION . '.project_questions.1.answer.option_answers')
            ->assertJsonCount(1, 'data.'. self::MUTATION . '.project_questions.0.answer.option_answers')
        ;
    }

    /** @test */
    public function success_protocol_issue_toggle_pending(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::ISSUE)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->setAnswerStatus(AnswerStatus::REJECT)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_2)->setAnswerStatus(AnswerStatus::DRAFT)->create();

        $answer_1 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)
            ->setText('first text')->create();
        $answer_2 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_2)
            ->setText('second text')->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text 1'
            ],
            [
                'project_protocol_question_id' => $projectProtocolQuestion_2->id,
                'text' => 'some text 2'
            ],
        ];

        $this->assertTrue($projectProtocol_1->isIssue());
        $this->assertTrue($projectProtocolQuestion_1->answerIsReject());
        $this->assertTrue($projectProtocolQuestion_2->answerIsDraft());

        $this->postGraphQL([
            'query' => $this->getQueryStrTwoTextFields($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $projectProtocol_1->id,
                        'status' => ProtocolStatus::PENDING,
                        'project_questions' => [
                            [
                                'id' => $projectProtocolQuestion_2->id,
                                'answer_status' => AnswerStatus::DRAFT,
                                'answer' => [
                                    'text' => data_get($data, '1.text'),
                                ]
                            ],
                            [
                                'id' => $projectProtocolQuestion_1->id,
                                'answer_status' => AnswerStatus::DRAFT,
                                'answer' => [
                                    'text' => data_get($data, '0.text'),
                                ]
                            ],
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_protocol_issue_not_toggle_pending_has_reject_answer(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();
        $question_3 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::ISSUE)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->setAnswerStatus(AnswerStatus::REJECT)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_2)->setAnswerStatus(AnswerStatus::DRAFT)->create();
        $projectProtocolQuestion_3 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_3)->setAnswerStatus(AnswerStatus::REJECT)->create();

        $answer_1 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)
            ->setText('first text')->create();
        $answer_2 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_2)
            ->setText('second text')->create();
        $answer_3 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_3)
            ->setText('third text')->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text 1'
            ],
            [
                'project_protocol_question_id' => $projectProtocolQuestion_2->id,
                'text' => 'some text 2'
            ],
        ];

        $this->assertTrue($projectProtocol_1->isIssue());
        $this->assertTrue($projectProtocolQuestion_1->answerIsReject());
        $this->assertTrue($projectProtocolQuestion_2->answerIsDraft());
        $this->assertTrue($projectProtocolQuestion_3->answerIsReject());

        $this->postGraphQL([
            'query' => $this->getQueryStrTwoTextFields($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $projectProtocol_1->id,
                        'status' => ProtocolStatus::ISSUE,
                        'project_questions' => [
                            [
                                'id' => $projectProtocolQuestion_3->id,
                                'answer_status' => AnswerStatus::REJECT,
                                'answer' => [
                                    'text' => $answer_3->text,
                                ]
                            ],
                            [
                                'id' => $projectProtocolQuestion_2->id,
                                'answer_status' => AnswerStatus::DRAFT,
                                'answer' => [
                                    'text' => data_get($data, '1.text'),
                                ]
                            ],
                            [
                                'id' => $projectProtocolQuestion_1->id,
                                'answer_status' => AnswerStatus::DRAFT,
                                'answer' => [
                                    'text' => data_get($data, '0.text'),
                                ]
                            ],
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrTwoTextFields(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    project_protocol_id: %s
                    input: [
                        {
                            project_protocol_question_id: %s,
                            text: "%s"
                        },
                        {
                            project_protocol_question_id: %s,
                            text: "%s"
                        }
                    ]
                ) {
                    id
                    status
                    project_questions {
                        id
                        answer_status
                        answer {
                            id
                            text
                        }
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'project_protocol_id'),
            data_get($data, '0.project_protocol_question_id'),
            data_get($data, '0.text'),
            data_get($data, '1.project_protocol_question_id'),
            data_get($data, '1.text'),
        );
    }

    /** @test */
    public function success_create_with_files_photo_type_required(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::REQUIRED)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text 1'
            ],
        ];

        $image1 = UploadedFile::fake()->image('product1.jpg');
        $image2 = UploadedFile::fake()->image('product2.jpg');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]) {%s (project_protocol_id: \"%s\", input: {project_protocol_question_id: \"%s\", text: \"%s\", media: $media}) {id, project_questions {answer{id, images{url}}}} }"}',
                self::MUTATION,
                data_get($data, 'project_protocol_id'),
                data_get($data, '0.project_protocol_question_id'),
                data_get($data, '0.text'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$image1, $image2],
        ];

        $id = $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $projectProtocol_1->id
                    ]
                ]
            ])
            ->assertJsonCount(2,'data.'.self::MUTATION.'.project_questions.0.answer.images')
            ->json('data.'.self::MUTATION.'.project_questions.0.answer.id')
        ;

        $this->assertDatabaseCount(Media::TABLE, 2);
        $this->assertDatabaseHas(Media::TABLE, ['model_type' => Answer::class, 'model_id' => $id]);
    }

    /** @test */
    public function success_create_with_files_photo_type_not_necessary(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY())->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text 1'
            ],
        ];

        $image1 = UploadedFile::fake()->image('product1.jpg');
        $image2 = UploadedFile::fake()->image('product2.jpg');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]) {%s (project_protocol_id: \"%s\", input: {project_protocol_question_id: \"%s\", text: \"%s\", media: $media}) {id, project_questions {answer{id, images{url}}}} }"}',
                self::MUTATION,
                data_get($data, 'project_protocol_id'),
                data_get($data, '0.project_protocol_question_id'),
                data_get($data, '0.text'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$image1, $image2],
        ];

        $id = $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $projectProtocol_1->id
                    ]
                ]
            ])
            ->assertJsonCount(2,'data.'.self::MUTATION.'.project_questions.0.answer.images')
            ->json('data.'.self::MUTATION.'.project_questions.0.answer.id')
        ;

        $this->assertDatabaseCount(Media::TABLE, 2);
        $this->assertDatabaseHas(Media::TABLE, ['model_type' => Answer::class, 'model_id' => $id]);
    }

    /** @test */
    public function success_create_with_files_photo_type_not_required(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_REQUIRED)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text 1'
            ],
        ];

        $image1 = UploadedFile::fake()->image('product1.jpg');
        $image2 = UploadedFile::fake()->image('product2.jpg');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]) {%s (project_protocol_id: \"%s\", input: {project_protocol_question_id: \"%s\", text: \"%s\", media: $media}) {id, project_questions {answer{id, images{url}}}} }"}',
                self::MUTATION,
                data_get($data, 'project_protocol_id'),
                data_get($data, '0.project_protocol_question_id'),
                data_get($data, '0.text'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$image1, $image2],
        ];

        $id = $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $projectProtocol_1->id
                    ]
                ]
            ])
            ->assertJsonCount(0,'data.'.self::MUTATION.'.project_questions.0.answer.images')
            ->json('data.'.self::MUTATION.'.project_questions.0.answer.id')
        ;

        $this->assertDatabaseCount(Media::TABLE, 0);
        $this->assertDatabaseMissing(Media::TABLE, ['model_type' => Answer::class, 'model_id' => $id]);
    }

    /** @test */
    public function not_update_if_question_status_accept(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::RADIO)->setProtocol($protocol_1)->create();
        $question_3 = $this->questionBuilder->setAnswerType(AnswerType::CHECKBOX)->setProtocol($protocol_1)->create();

        $option_answer_1 = $this->optionAnswerBuilder->setQuestion($question_3)->create();
        $option_answer_2 = $this->optionAnswerBuilder->setQuestion($question_3)->create();
        $option_answer_3 = $this->optionAnswerBuilder->setQuestion($question_3)->create();

        $option_answer_4 = $this->optionAnswerBuilder->setQuestion($question_2)->create();
        $option_answer_5 = $this->optionAnswerBuilder->setQuestion($question_2)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->setAnswerStatus(AnswerStatus::ACCEPT)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_2)->setAnswerStatus(AnswerStatus::ACCEPT)->create();
        $projectProtocolQuestion_3 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_3)->setAnswerStatus(AnswerStatus::ACCEPT)->create();

        $answer_1 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)
            ->setText('first text')->create();
        $answer_2 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_2)
            ->setOptionAnswers($option_answer_4)->create();
        $answer_3 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_3)
            ->setOptionAnswers($option_answer_1, $option_answer_3)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text 1'
            ],
            [
                'project_protocol_question_id' => $projectProtocolQuestion_2->id,
                'option_answer_ids' => [
                    $option_answer_5->id,
                ]
            ],
            [
                'project_protocol_question_id' => $projectProtocolQuestion_3->id,
                'option_answer_ids' => [
                    $option_answer_2->id,
                ]
            ]
        ];

        $this->assertNotEquals($projectProtocolQuestion_1->answer->text, data_get($data, '0.text'));
        $this->assertCount(1, $projectProtocolQuestion_2->answer->optionAnswers);
        $this->assertCount(2, $projectProtocolQuestion_3->answer->optionAnswers);

        $this->assertEquals($projectProtocol_1->status, ProtocolStatus::PENDING);

        $this->assertTrue($projectProtocolQuestion_1->answerIsAccept());
        $this->assertTrue($projectProtocolQuestion_2->answerIsAccept());
        $this->assertTrue($projectProtocolQuestion_3->answerIsAccept());

        $this->postGraphQL([
            'query' => $this->getQueryStrSomeFields($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $projectProtocol_1->id,
                        'status' => ProtocolStatus::PENDING,
                        'project_questions' => [
                            [
                                'id' => $projectProtocolQuestion_3->id,
                                'answer_status' => AnswerStatus::ACCEPT,
                                'answer' => [
                                    'text' => null,
                                    'option_answers' => [
                                        ['id' => $option_answer_1->id],
                                        ['id' => $option_answer_3->id],
                                    ],
                                ]
                            ],
                            [
                                'id' => $projectProtocolQuestion_2->id,
                                'answer_status' => AnswerStatus::ACCEPT,
                                'answer' => [
                                    'option_answers' => [
                                        ['id' => $option_answer_4->id]
                                    ],
                                ]
                            ],
                            [
                                'id' => $projectProtocolQuestion_1->id,
                                'answer_status' => AnswerStatus::ACCEPT,
                                'answer' => [
                                    'text' => $projectProtocolQuestion_1->answer->text,
                                    'option_answers' => [],
                                ]
                            ],
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(0, 'data.'. self::MUTATION . '.project_questions.2.answer.option_answers')
            ->assertJsonCount(1, 'data.'. self::MUTATION . '.project_questions.1.answer.option_answers')
            ->assertJsonCount(2, 'data.'. self::MUTATION . '.project_questions.0.answer.option_answers')
        ;
    }

    protected function getQueryStrSomeFields(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    project_protocol_id: %s
                    input: [
                        {
                            project_protocol_question_id: %s,
                            text: "%s"
                        },
                        {
                            project_protocol_question_id: %s,
                            option_answer_ids: [%s]
                        },
                        {
                            project_protocol_question_id: %s,
                            option_answer_ids: [%s, %s]
                        }
                    ]
                ) {
                    id
                    status
                    project_questions {
                        id
                        answer_status
                        answer {
                            id
                            text
                            option_answers {
                                id
                            }
                        }
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'project_protocol_id'),
            data_get($data, '0.project_protocol_question_id'),
            data_get($data, '0.text'),
            data_get($data, '1.project_protocol_question_id'),
            data_get($data, '1.option_answer_ids.0'),
            data_get($data, '2.project_protocol_question_id'),
            data_get($data, '2.option_answer_ids.0'),
            data_get($data, '2.option_answer_ids.1'),
        );
    }

//    /** @test */
//    public function fail_image_as_string(): void
//    {
//        $this->loginAsTechnicianWithRole();
//
//        $date = CarbonImmutable::now();
//        /** @var $project CommercialProject */
//        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();
//
//        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
//
//        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();
//
//        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();
//
//        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
//            ->setQuestion($question_1)->create();
//
//        $option_answer_5 = $this->optionAnswerBuilder->setQuestion($question_1)->create();
//
//        $data = [
//            'project_protocol_id' => $projectProtocol_1->id,
//            [
//                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
//                'option_answer_ids' => [
//                    $option_answer_5->id,
//                ],
//                'media' => [
//                    [
//                        'name' => 'some name'
//                    ]
//                ]
//            ],
//        ];
//
//        $this->postGraphQL([
//            'query' => $this->getQueryStrImageAsString($data)
//        ])
//            ->dump()
//            ->assertJson([
//                'errors' => [
//                    ['message' => __('exceptions.commercial.commissioning.answer must contain a text field')]
//                ]
//            ]);
//    }

    protected function getQueryStrImageAsString(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    project_protocol_id: %s
                    input: [{
                        project_protocol_question_id: %s,
                        option_answer_ids: [%s]
                        medial: [
                            [name: %s]
                        ]
                    }]
                ) {
                    id

                }
            }',
            self::MUTATION,
            data_get($data, 'project_protocol_id'),
            data_get($data, '0.project_protocol_question_id'),
            data_get($data, '0.option_answer_ids.0'),
            data_get($data, '0.media.0.name'),
        );
    }

    /** @test */
    public function fail_create_radio_but_type_text(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $option_answer_4 = $this->optionAnswerBuilder->setQuestion($question_1)->create();
        $option_answer_5 = $this->optionAnswerBuilder->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'option_answer_ids' => [
                    $option_answer_5->id,
                ]
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrRadio($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                'questions.0.text' => [__('exceptions.commercial.commissioning.answer must contain a text field')]
                            ]
                        ],
                    ]
                ]
            ]);
    }

    /** @test */
    public function fail_create_radio_but_type_text_second_error(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_REQUIRED)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::NOT_REQUIRED)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_2)->create();

        $option_answer_4 = $this->optionAnswerBuilder->setQuestion($question_2)->create();
        $option_answer_5 = $this->optionAnswerBuilder->setQuestion($question_2)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some_text'
            ],
            [
                'project_protocol_question_id' => $projectProtocolQuestion_2->id,
                'option_answer_ids' => [
                    $option_answer_5->id,
                ]
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrRadioTwoFields($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                'questions.1.text' => [__('exceptions.commercial.commissioning.answer must contain a text field')]
                            ]
                        ],
                    ]
                ]
            ]);
    }

    /** @test */
    public function fail_create_radio_but_another_option(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::RADIO)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::RADIO)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_2)->create();

        $option_answer_1 = $this->optionAnswerBuilder->setQuestion($question_1)->create();

        $option_answer_4 = $this->optionAnswerBuilder->setQuestion($question_2)->create();
        $option_answer_5 = $this->optionAnswerBuilder->setQuestion($question_2)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'option_answer_ids' => [
                    $option_answer_5->id,
                ]
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrRadio($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                'questions.0.option_answer_ids' => [__('exceptions.commercial.commissioning.option answer does not apply to this question')]
                            ]
                        ],
                    ]
                ]
            ]);
    }

    protected function getQueryStrRadio(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    project_protocol_id: %s
                    input: [{
                        project_protocol_question_id: %s,
                        option_answer_ids: [%s]
                    }]
                ) {
                    id

                }
            }',
            self::MUTATION,
            data_get($data, 'project_protocol_id'),
            data_get($data, '0.project_protocol_question_id'),
            data_get($data, '0.option_answer_ids.0'),
        );
    }

    protected function getQueryStrRadioTwoFields(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    project_protocol_id: %s
                    input: [
                        {
                            project_protocol_question_id: %s,
                            text: "%s"
                        },
                        {
                            project_protocol_question_id: %s,
                            option_answer_ids: [%s]
                        }
                    ]
                ) {
                    id

                }
            }',
            self::MUTATION,
            data_get($data, 'project_protocol_id'),
            data_get($data, '0.project_protocol_question_id'),
            data_get($data, '0.text'),
            data_get($data, '1.project_protocol_question_id'),
            data_get($data, '1.option_answer_ids.0'),
        );
    }

    /** @test */
    public function fail_create_radio_more_as_one_options(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setPhotoType(AnswerPhotoType::NOT_NECESSARY)
            ->setAnswerType(AnswerType::RADIO)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $option_answer_4 = $this->optionAnswerBuilder->setQuestion($question_1)->create();
        $option_answer_5 = $this->optionAnswerBuilder->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'option_answer_ids' => [
                    $option_answer_4->id,
                    $option_answer_5->id,
                ]
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrRadioTwoOptions($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                'questions.0.option_answer_ids' => ["This answer must contain one option answer"]
                            ]
                        ],
                    ]
                ]
            ]);
    }

    protected function getQueryStrRadioTwoOptions(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    project_protocol_id: %s
                    input: [{
                        project_protocol_question_id: %s,
                        option_answer_ids: [%s, %s]
                    }]
                ) {
                    id

                }
            }',
            self::MUTATION,
            data_get($data, 'project_protocol_id'),
            data_get($data, '0.project_protocol_question_id'),
            data_get($data, '0.option_answer_ids.0'),
            data_get($data, '0.option_answer_ids.1'),
        );
    }

    /** @test */
    public function fail_create_text_but_type_radio(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::RADIO)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text'
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrText($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                'questions.0.text' => [__('exceptions.commercial.commissioning.answer must contain an options answer field')]
                            ]
                        ],
                    ]
                ]
            ]);
    }

    /** @test */
    public function fail_create_photo_required(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setPhotoType(AnswerPhotoType::REQUIRED)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text'
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrText($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                'questions.0.media' => [ __('exceptions.commercial.commissioning.answer must contain a media')]
                            ]
                        ],
                    ]
                ]
            ]);
    }

    protected function getQueryStrText(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    project_protocol_id: %s
                    input: [{
                        project_protocol_question_id: "%s"
                        text: "%s"
                    }]
                ) {
                    id

                }
            }',
            self::MUTATION,
            data_get($data, 'project_protocol_id'),
            data_get($data, '0.project_protocol_question_id'),
            data_get($data, '0.text'),
        );
    }

    /** @test */
    public function fail_create_text_but_type_option_answer(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::CHECKBOX)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => '1'
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrText($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                'questions.0.text' => [__('exceptions.commercial.commissioning.answer must contain an options answer field')]
                            ]
                        ],
                    ]
                ]
            ]);
    }

    /** @test */
    public function fail_protocol_is_closed(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::DONE)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text'
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrText($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.this protocol is closed')]
                ]
            ]);
    }

    /** @test */
    public function fail_end_commissioning(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setEndCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::DONE)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text'
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrText($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.commissioning for this project is closed')]
                ]
            ]);
    }

    /** @test */
    public function fail_commissioning_not_started_yet(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::DONE)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'text' => 'some text'
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrText($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.commissioning not started yet')]
                ]
            ]);
    }

    /** @test */
    public function fail_create_select_but_another_option(): void
    {
        $this->loginAsTechnicianWithRole();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::CHECKBOX)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::CHECKBOX)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_2)->create();

        $option_answer_1 = $this->optionAnswerBuilder->setQuestion($question_1)->create();

        $option_answer_4 = $this->optionAnswerBuilder->setQuestion($question_2)->create();
        $option_answer_5 = $this->optionAnswerBuilder->setQuestion($question_2)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'option_answer_ids' => [
                    $option_answer_5->id,
                ]
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrOption($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                'questions.0.option_answer_ids' => [__('exceptions.commercial.commissioning.option answer does not apply to this question')]
                            ]
                        ],
                    ]
                ]
            ]);
    }

    /** @test */
    public function fail_technic_not_have_certificate(): void
    {
        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::CHECKBOX)
            ->setPhotoType(AnswerPhotoType::NOT_NECESSARY)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();

        $option_answer_1 = $this->optionAnswerBuilder->setQuestion($question_1)->create();

        $data = [
            'project_protocol_id' => $projectProtocol_1->id,
            [
                'project_protocol_question_id' => $projectProtocolQuestion_1->id,
                'option_answer_ids' => [
                    $option_answer_1->id,
                ]
            ],
        ];

        $this->postGraphQL([
            'query' => $this->getQueryStrOption($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }

    protected function getQueryStrOption(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    project_protocol_id: %s
                    input: [{
                        project_protocol_question_id: "%s"
                        option_answer_ids: [%s]
                    }]
                ) {
                    id

                }
            }',
            self::MUTATION,
            data_get($data, 'project_protocol_id'),
            data_get($data, '0.project_protocol_question_id'),
            data_get($data, '0.option_answer_ids.0'),
        );
    }
}
