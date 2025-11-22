<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Enums\Commercial\Commissioning\AnswerType;
use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Enums\Commercial\Commissioning\ProtocolType;
use App\Events\Commercial\Commissioning\AnswerRejectedEvent;
use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\SetStatusProjectQuestionMutation;
use App\Listeners\Alerts\AlertEventsListener;
use App\Models\Commercial\CommercialProject;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Commercial\Commissioning\AnswerBuilder;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolQuestionBuilder;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;

class SetStatusProjectQuestionMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SetStatusProjectQuestionMutation::NAME;

    protected $protocolBuilder;
    protected $projectBuilder;
    protected $protocolProjectBuilder;
    protected $protocolProjectQuestionBuilder;
    protected $questionBuilder;
    protected $answerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->questionBuilder = resolve(QuestionBuilder::class);
        $this->protocolProjectBuilder = resolve(ProjectProtocolBuilder::class);
        $this->protocolProjectQuestionBuilder = resolve(ProjectProtocolQuestionBuilder::class);
        $this->answerBuilder = resolve(AnswerBuilder::class);
    }

    /** @test */
    public function success_set_status_accept_not_close_protocol(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_2)->create();

        $answer = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)->create();

        $data = [
            'project_protocol_question_id' => $projectProtocolQuestion_1->id,
            'status' => AnswerStatus::ACCEPT
        ];

        $this->assertTrue($projectProtocol_1->isPending());
        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => data_get($data, 'project_protocol_question_id'),
                        'answer_status' => data_get($data, 'status'),
                    ],
                ]
            ])
        ;

        $projectProtocolQuestion_1->refresh();
        $projectProtocol_1->refresh();

        $this->assertTrue($projectProtocolQuestion_1->answerIsAccept());
        $this->assertTrue($projectProtocol_1->isPending());
    }

    /** @test */
    public function success_set_status_accept_close_protocol_not_close_pre_commissioning(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $protocol_2 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();
        $projectProtocol_2 = $this->protocolProjectBuilder->setProtocol($protocol_2)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::ACCEPT)->setQuestion($question_2)->create();

        $answer = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)->create();

        $data = [
            'project_protocol_question_id' => $projectProtocolQuestion_1->id,
            'status' => AnswerStatus::ACCEPT
        ];

        $this->assertTrue($project->isStartPreCommissioning());
        $this->assertFalse($project->isStartCommissioning());
        $this->assertTrue($projectProtocol_1->isPending());
        $this->assertTrue($projectProtocol_2->isPending());
        $this->assertNull($projectProtocol_2->closed_at);
        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => data_get($data, 'project_protocol_question_id'),
                        'answer_status' => data_get($data, 'status'),
                    ],
                ]
            ])
        ;

        $projectProtocolQuestion_1->refresh();
        $projectProtocol_1->refresh();

        $this->assertTrue($projectProtocolQuestion_1->answerIsAccept());
        $this->assertTrue($projectProtocol_1->isDone());
        $this->assertNotNull($projectProtocol_1->closed_at);
    }

    /** @test */
    public function success_set_status_accept_close_protocol_close_pre_commissioning(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $protocol_2 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();
        $projectProtocol_2 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::DONE)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::ACCEPT)->setQuestion($question_2)->create();

        $answer = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)->create();

        $data = [
            'project_protocol_question_id' => $projectProtocolQuestion_1->id,
            'status' => AnswerStatus::ACCEPT
        ];

        $this->assertTrue($project->isStartPreCommissioning());
        $this->assertFalse($project->isStartCommissioning());
        $this->assertTrue($projectProtocol_1->isPending());
        $this->assertTrue($projectProtocol_2->isDone());
        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => data_get($data, 'project_protocol_question_id'),
                        'answer_status' => data_get($data, 'status'),
                    ],
                ]
            ])
        ;

        $projectProtocolQuestion_1->refresh();
        $projectProtocol_1->refresh();
        $project->refresh();

        $this->assertTrue($project->isStartCommissioning());
        $this->assertTrue($projectProtocolQuestion_1->answerIsAccept());
        $this->assertTrue($projectProtocol_1->isDone());
    }

    /** @test */
    public function success_set_status_accept_close_protocol_close_commissioning(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::COMMISSIONING)->create();
        $protocol_2 = $this->protocolBuilder->setType(ProtocolType::COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();
        $projectProtocol_2 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::DONE)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::ACCEPT)->setQuestion($question_2)->create();

        $data = [
            'project_protocol_question_id' => $projectProtocolQuestion_1->id,
            'status' => AnswerStatus::ACCEPT
        ];

        $answer = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)->create();

        $this->assertTrue($project->isStartCommissioning());
        $this->assertFalse($project->isEndCommissioning());
        $this->assertTrue($projectProtocol_1->isPending());
        $this->assertTrue($projectProtocol_2->isDone());
        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => data_get($data, 'project_protocol_question_id'),
                        'answer_status' => data_get($data, 'status'),
                    ],
                ]
            ])
        ;

        $projectProtocolQuestion_1->refresh();
        $projectProtocol_1->refresh();
        $project->refresh();

        $this->assertTrue($project->isEndCommissioning());
        $this->assertTrue($projectProtocolQuestion_1->answerIsAccept());
        $this->assertTrue($projectProtocol_1->isDone());
    }

    /** @test */
    public function success_set_status_reject(): void
    {
//        Event::fake([AnswerRejectedEvent::class]);

        $admin = $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();

        $answer = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)->create();

        $data = [
            'project_protocol_question_id' => $projectProtocolQuestion_1->id,
            'status' => AnswerStatus::REJECT
        ];

        $this->assertTrue($projectProtocol_1->isPending());
        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => data_get($data, 'project_protocol_question_id'),
                        'answer_status' => data_get($data, 'status'),
                    ],
                ]
            ])
        ;

        $projectProtocolQuestion_1->refresh();
        $projectProtocol_1->refresh();

        $this->assertTrue($projectProtocol_1->isIssue());
        $this->assertTrue($projectProtocolQuestion_1->answerIsReject());

//        Event::assertDispatched(function (AnswerRejectedEvent $event) use($admin, $projectProtocolQuestion_1) {
//            return $event->getUser()->id === $admin->id
//                && $event->getInitiator()->id === $admin->id
//                && $event->getProjectProtocolQuestion()->id === $projectProtocolQuestion_1->id
//                && $event->getModel()->id === $projectProtocolQuestion_1->id
//                ;
//        });
//        Event::assertListening(AnswerRejectedEvent::class, AlertEventsListener::class);
    }

    /** @test */
    public function fail_protocol_is_closed(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::DONE)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();

        $answer = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)->create();

        $data = [
            'project_protocol_question_id' => $projectProtocolQuestion_1->id,
            'status' => AnswerStatus::REJECT
        ];

        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.this protocol is closed')]
                ]
            ]);

        $projectProtocolQuestion_1->refresh();

        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    project_protocol_question_id: %s
                    status: %s
                ) {
                    id
                    answer_status
                }
            }',
            self::MUTATION,
            data_get($data, 'project_protocol_question_id'),
            data_get($data, 'status'),
        );
    }

    /** @test */
    public function fail_status_in_not_support(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::DONE)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();

        $answer = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_1)->create();

        $data = [
            'project_protocol_question_id' => $projectProtocolQuestion_1->id,
            'status' => AnswerStatus::NONE
        ];

        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                'status' => ["The selected status is invalid."]
                            ]
                        ],
                    ]
                ]
            ]);

        $projectProtocolQuestion_1->refresh();

        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());
    }

    /** @test */
    public function fail_set_status_if_not_have_answer(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();
        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::TEXT)
            ->setProtocol($protocol_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($question_1)->create();

        $data = [
            'project_protocol_question_id' => $projectProtocolQuestion_1->id,
            'status' => AnswerStatus::REJECT
        ];

        $this->assertNull($projectProtocolQuestion_1->answer);
        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __('exceptions.commercial.commissioning.question does not contain an answer')]
                ]
            ]);

        $projectProtocolQuestion_1->refresh();

        $this->assertTrue($projectProtocolQuestion_1->answerIsDraft());
    }
}

