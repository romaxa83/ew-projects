<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\CommercialProjects;

use App\Enums\Commercial\Commissioning\ProtocolType;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\StartCommissioningMutation;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\Protocol;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;

class StartCommissioningMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = StartCommissioningMutation::NAME;

    protected ProtocolBuilder $protocolBuilder;
    protected QuestionBuilder $questionBuilder;
    protected ProjectBuilder $projectBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->questionBuilder = resolve(QuestionBuilder::class);
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
        $this->projectBuilder = resolve(ProjectBuilder::class);
    }

    /** @test */
    public function success_start(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->create();

        $protocol_1 = $this->protocolBuilder->setSort(1)->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $protocol_2 = $this->protocolBuilder->setSort(2)->setType(ProtocolType::COMMISSIONING)->create();
        $protocol_3 = $this->protocolBuilder->setSort(3)->setType(ProtocolType::COMMISSIONING)->create();

        $q_1 = $this->questionBuilder->setSort(1)->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $q_2 = $this->questionBuilder->setSort(2)->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $q_3 = $this->questionBuilder->setSort(3)->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $q_4 = $this->questionBuilder->setSort(4)->setProtocol($protocol_2)->setStatus(QuestionStatus::ACTIVE())->create();
        $q_5 = $this->questionBuilder->setSort(5)->setProtocol($protocol_2)->setStatus(QuestionStatus::ACTIVE())->create();
        $q_6 = $this->questionBuilder->setSort(6)->setProtocol($protocol_3)->setStatus(QuestionStatus::ACTIVE())->create();

        $this->assertNull($project->start_pre_commissioning_date);
        $this->assertNull($project->end_pre_commissioning_date);
        $this->assertNull($project->start_commissioning_date);
        $this->assertNull($project->end_commissioning_date);

        $this->assertEmpty($project->protocols);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($project->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'success',
                        'message' => __('messages.commercial.commissioning_start'),
                    ],
                ]
            ])
        ;

        $project->refresh();

        $this->assertNotNull($project->start_pre_commissioning_date);
        $this->assertNull($project->end_pre_commissioning_date);
        $this->assertNull($project->start_commissioning_date);
        $this->assertNull($project->end_commissioning_date);

        $protocols = Protocol::query()->get();

        $this->assertNotEmpty($project->projectProtocols);

        $this->assertEquals($project->projectProtocols[0]->sort, $protocol_3->sort);
        $this->assertEquals($project->projectProtocols[1]->sort, $protocol_2->sort);
        $this->assertEquals($project->projectProtocols[2]->sort, $protocol_1->sort);

        $this->assertCount($protocols->count(), $project->projectProtocols);
        $this->assertCount(
            $protocols->where('type', ProtocolType::PRE_COMMISSIONING)->count(),
            $project->projectProtocolsPreCommissioning
        );
        $this->assertCount(
            $protocols->where('type', ProtocolType::COMMISSIONING)->count(),
            $project->projectProtocolsCommissioning
        );

        $this->assertCount(3, $project->projectProtocols[2]->projectQuestions);
        $this->assertEquals($project->projectProtocols[2]->projectQuestions[0]->sort, $q_3->sort);
        $this->assertEquals($project->projectProtocols[2]->projectQuestions[1]->sort, $q_2->sort);
        $this->assertEquals($project->projectProtocols[2]->projectQuestions[2]->sort, $q_1->sort);

        $this->assertCount(2, $project->projectProtocols[1]->projectQuestions);
        $this->assertEquals($project->projectProtocols[1]->projectQuestions[0]->sort, $q_5->sort);
        $this->assertEquals($project->projectProtocols[1]->projectQuestions[1]->sort, $q_4->sort);

        $this->assertCount(1, $project->projectProtocols[0]->projectQuestions);
        $this->assertEquals($project->projectProtocols[0]->projectQuestions[0]->sort, $q_6->sort);
    }

    /** @test */
    public function attach_only_active_question(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $protocol_2 = $this->protocolBuilder->setType(ProtocolType::COMMISSIONING)->create();

        $q_1 = $this->questionBuilder->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $q_2 = $this->questionBuilder->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $q_3 = $this->questionBuilder->setProtocol($protocol_1)->setStatus(QuestionStatus::DRAFT())->create();
        $q_4 = $this->questionBuilder->setProtocol($protocol_1)->setStatus(QuestionStatus::INACTIVE())->create();
        $q_5 = $this->questionBuilder->setProtocol($protocol_2)->setStatus(QuestionStatus::INACTIVE())->create();
        $q_6 = $this->questionBuilder->setProtocol($protocol_2)->setStatus(QuestionStatus::ACTIVE())->create();

        $this->assertEmpty($project->protocols);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($project->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'success',
                        'message' => __('messages.commercial.commissioning_start'),
                    ],
                ]
            ])
        ;

        $project->refresh();

        $this->assertNotEmpty($project->projectProtocols);
        $this->assertCount(2, $project->projectProtocols);

        $this->assertCount(2, $project->projectProtocols[1]->projectQuestions);

        $this->assertEquals($project->projectProtocols[1]->projectQuestions[1]->question_id, $q_1->id);
        $this->assertEquals($project->projectProtocols[1]->projectQuestions[0]->question_id, $q_2->id);

        $this->assertCount(1, $project->projectProtocols[0]->projectQuestions);
        $this->assertEquals($project->projectProtocols[0]->projectQuestions[0]->question_id, $q_6->id);
    }

    /** @test */
    public function fail_project_is_start_commissioning(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setData([
            'start_pre_commissioning_date' => CarbonImmutable::now()
        ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($project->id)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'type' => 'warning',
                            'message' => __('messages.commercial.is_commissioning_start'),
                        ],
                    ]
                ]
            )
        ;
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s,
                ) {
                    type
                    message
                }
            }',
            self::MUTATION,
            $id,
        );
    }
}

