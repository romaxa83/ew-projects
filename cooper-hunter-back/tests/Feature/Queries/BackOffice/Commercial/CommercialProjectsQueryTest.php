<?php

namespace Tests\Feature\Queries\BackOffice\Commercial;

use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Enums\Commercial\Commissioning\AnswerType;
use App\Enums\Commercial\Commissioning\ProtocolType;
use App\GraphQL\Queries\BackOffice\Commercial\CommercialProjectsQuery;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectAddition;
use App\Models\Technicians\Technician;
use App\Models\Warranty\WarrantyRegistration;
use Carbon\CarbonImmutable;
use Tests\Builders\Commercial\Commissioning\AnswerBuilder;
use Tests\Builders\Commercial\Commissioning\OptionAnswerBuilder;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolQuestionBuilder;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\Builders\Commercial\ProjectAdditionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\Feature\Queries\Common\Commercial\AbstractCommercialProjectsQueryTest;

class CommercialProjectsQueryTest extends AbstractCommercialProjectsQueryTest
{
    public const QUERY = CommercialProjectsQuery::NAME;

    protected ProtocolBuilder $protocolBuilder;
    protected $protocolProjectBuilder;
    protected $protocolProjectQuestionBuilder;
    protected $projectBuilder;
    protected $optionAnswerBuilder;
    protected $answerBuilder;
    protected $questionBuilder;

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
        $this->projectAdditionBuilder = resolve(ProjectAdditionBuilder::class);
    }

    public function test_get_list(): void
    {
        $this->loginAsSuperAdmin();

        CommercialProject::factory()
            ->times(10)
            ->create();

        $this->postGraphQLBackOffice(
            $this->getQuery(self::QUERY)
        )
            ->assertOk()
            ->assertJsonCount(10, 'data.' . self::QUERY . '.data');
    }

    /** @test */
    public function show_one_if_deleted_technicians(): void
    {
        $this->loginAsSuperAdmin();

        $tech = Technician::factory()->create([
            'deleted_at' => CarbonImmutable::now()
        ]);

        $project = $this->projectBuilder->setTechnician($tech)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($project->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                           [
                               'id' => $project->id,
                               'member' => null
                           ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_filter_by_code(): void
    {
        $this->loginAsSuperAdmin();

        $project = CommercialProject::factory()
            ->times(10)
            ->create()
            ->first();

        $project->code = 'someuniqcode';
        $project->save();

        $this->postGraphQLBackOffice(
            $this->getQuery(self::QUERY, ['query' => $project->code])
        )
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data')
            ->assertJsonCount(0, 'data.' . self::QUERY . '.data.0.project_protocols');
    }

    /** @test */
    public function with_protocols(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $protocol_2 = $this->protocolBuilder->setType(ProtocolType::COMMISSIONING)->create();

        $question_1 = $this->questionBuilder->setAnswerType(AnswerType::CHECKBOX)->setProtocol($protocol_1)->create();
        $question_2 = $this->questionBuilder->setProtocol($protocol_1)->create();
        $question_3 = $this->questionBuilder->setProtocol($protocol_1)->create();
        $question_4 = $this->questionBuilder->setProtocol($protocol_2)->create();
        $question_5 = $this->questionBuilder->setProtocol($protocol_2)->create();
        $question_6 = $this->questionBuilder->setProtocol($protocol_2)->create();
        $question_7 = $this->questionBuilder->setProtocol($protocol_2)->create();

        $option_answer_1 = $this->optionAnswerBuilder->setQuestion($question_1)->create();
        $option_answer_2 = $this->optionAnswerBuilder->setQuestion($question_1)->create();
        $option_answer_3 = $this->optionAnswerBuilder->setQuestion($question_1)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)->setProject($project)->create();
        $projectProtocol_2 = $this->protocolProjectBuilder->setProtocol($protocol_2)->setProject($project)->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setQuestion($question_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::ACCEPT)->setQuestion($question_2)->create();
        $projectProtocolQuestion_3 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::ACCEPT)->setQuestion($question_3)->create();
        $projectProtocolQuestion_4 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_2)
            ->setQuestion($question_4)->create();
        $projectProtocolQuestion_5 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_2)
            ->setQuestion($question_5)->create();
        $projectProtocolQuestion_6 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_2)
            ->setAnswerStatus(AnswerStatus::ACCEPT)->setQuestion($question_6)->create();
        $projectProtocolQuestion_7 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_2)
            ->setAnswerStatus(AnswerStatus::REJECT)->setQuestion($question_7)->create();

        $answer_1 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_2)->create();
        $answer_2 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_3)->create();
        $answer_3 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_6)->create();
        $answer_4 = $this->answerBuilder->setProjectProtocolQuestion($projectProtocolQuestion_7)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithProtocol($project->id)
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $project->id,
                                'start_pre_commissioning_date' => $date->format('Y-m-d'),
                                'end_pre_commissioning_date' => null,
                                'start_commissioning_date' => null,
                                'end_commissioning_date' => null,
                                'project_protocols' => [
                                    [
                                        'id' => $projectProtocol_2->id,
                                        'status' => $projectProtocol_1->status,
                                        'protocol' => [
                                            'id' => $protocol_2->id
                                        ],
                                        'total_questions' => 4,
                                        'total_correct_answers' => 1,
                                        'total_wrong_answers' => 1,
                                        'project_questions' => [
                                            [
                                                'id' => $projectProtocolQuestion_7->id,
                                                'answer_status' => $projectProtocolQuestion_7->answer_status,
                                                'question' => [
                                                    'id' => $question_7->id
                                                ],
                                                'answer' => ['id' => $answer_4->id]
                                            ],
                                            [
                                                'id' => $projectProtocolQuestion_6->id,
                                                'answer_status' => $projectProtocolQuestion_6->answer_status,
                                                'question' => [
                                                    'id' => $question_6->id
                                                ],
                                                'answer' => ['id' => $answer_3->id]
                                            ],
                                            [
                                                'id' => $projectProtocolQuestion_5->id,
                                                'answer_status' => $projectProtocolQuestion_5->answer_status,
                                                'question' => [
                                                    'id' => $question_5->id
                                                ],
                                                'answer' => null
                                            ],
                                            [
                                                'id' => $projectProtocolQuestion_4->id,
                                                'answer_status' => $projectProtocolQuestion_4->answer_status,
                                                'question' => [
                                                    'id' => $question_4->id
                                                ],
                                                'answer' => null
                                            ],
                                        ]
                                    ],
                                    [
                                        'id' => $projectProtocol_1->id,
                                        'status' => $projectProtocol_1->status,
                                        'protocol' => [
                                            'id' => $protocol_1->id
                                        ],
                                        'total_questions' => 3,
                                        'total_correct_answers' => 2,
                                        'total_wrong_answers' => 0,
                                        'project_questions' => [
                                            [
                                                'id' => $projectProtocolQuestion_3->id,
                                                'answer_status' => $projectProtocolQuestion_3->answer_status,
                                                'question' => [
                                                    'id' => $question_3->id,
                                                ],
                                                'answer' => ['id' => $answer_2->id]
                                            ],
                                            [
                                                'id' => $projectProtocolQuestion_2->id,
                                                'answer_status' => $projectProtocolQuestion_2->answer_status,
                                                'question' => [
                                                    'id' => $question_2->id,
                                                    'option_answers' => [],
                                                ],
                                                'answer' => ['id' => $answer_1->id]
                                            ],
                                            [
                                                'id' => $projectProtocolQuestion_1->id,
                                                'answer_status' => $projectProtocolQuestion_1->answer_status,
                                                'question' => [
                                                    'id' => $question_1->id,
                                                    'option_answers' => [
                                                        ['id' => $option_answer_1->id],
                                                        ['id' => $option_answer_2->id],
                                                        ['id' => $option_answer_3->id],
                                                    ]
                                                ]
                                            ],
                                        ]
                                    ]
                                ],
                                'project_protocols_pre_commissioning' => [
                                    [
                                        'id' => $projectProtocol_1->id,
                                    ],
                                ],
                                'project_protocols_commissioning' => [
                                    [
                                        'id' => $projectProtocol_2->id,
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data');
    }

    protected function getQueryStrWithProtocol($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                       data {
                            id
                            start_pre_commissioning_date
                            end_pre_commissioning_date
                            start_commissioning_date
                            end_commissioning_date
                            project_protocols {
                                id
                                status
                                closed_at
                                protocol {
                                    id
                                }
                                total_questions
                                total_correct_answers
                                total_wrong_answers
                                project_questions {
                                    id
                                    answer_status
                                    question {
                                        id
                                        option_answers {
                                            id
                                        }
                                    }
                                    answer {
                                        id
                                    }
                                }
                            }
                            project_protocols_pre_commissioning {
                                id
                            }
                            project_protocols_commissioning {
                                id
                            }
                       }
                       meta {
                            total
                       }
                }
            }',
            self::QUERY,
            $id
        );
    }

    protected function getQueryStrById($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                       data {
                            id
                            member {
                                id
                            }
                       }
                       meta {
                            total
                       }
                }
            }',
            self::QUERY,
            $id
        );
    }

    /** @test */
    public function with_addition_can_update(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();

        /** @var $addition CommercialProjectAddition */
        $addition = $this->projectAdditionBuilder->setProject($project)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithAddition($project->id)
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $project->id,
                                'additions' => [
                                    'installer_license_number' => $addition->installer_license_number,
                                    'purchase_place' => $addition->purchase_place,
                                    'purchase_date' => $addition->purchase_date->format('Y-m-d'),
                                    'installation_date' => $addition->installation_date->format('Y-m-d'),
                                    'can_update' => true,
                                ],
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data');
    }

    /** @test */
    public function with_addition_can_not_update(): void
    {
        $this->loginAsSuperAdmin();

        $date = CarbonImmutable::now();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setStartPreCommissioningDate($date)->create();
        WarrantyRegistration::factory()->create([
            'commercial_project_id' => $project->id
        ]);

        /** @var $addition CommercialProjectAddition */
        $addition = $this->projectAdditionBuilder->setProject($project)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithAddition($project->id)
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            [
                                'id' => $project->id,
                                'additions' => [
                                    'installer_license_number' => $addition->installer_license_number,
                                    'purchase_place' => $addition->purchase_place,
                                    'purchase_date' => $addition->purchase_date->format('Y-m-d'),
                                    'installation_date' => $addition->installation_date->format('Y-m-d'),
                                    'can_update' => false,
                                ],
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data');
    }

    protected function getQueryStrWithAddition($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                       data {
                            id
                            additions {
                                installer_license_number
                                purchase_place
                                purchase_date
                                installation_date
                                can_update
                            }
                       }
                       meta {
                            total
                       }
                }
            }',
            self::QUERY,
            $id
        );
    }
}
