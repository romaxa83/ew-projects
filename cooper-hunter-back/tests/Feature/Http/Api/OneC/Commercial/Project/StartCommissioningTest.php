<?php

namespace Tests\Feature\Http\Api\OneC\Commercial\Project;

use App\Enums\Commercial\Commissioning\ProtocolType;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\Protocol;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;

class StartCommissioningTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

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
    public function success_start_commissioning(): void
    {
        $this->loginAsModerator();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $protocol_1 = $this->protocolBuilder->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $protocol_2 = $this->protocolBuilder->setType(ProtocolType::COMMISSIONING)->create();
        $protocol_3 = $this->protocolBuilder->setType(ProtocolType::COMMISSIONING)->create();

        $this->questionBuilder->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $this->questionBuilder->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $this->questionBuilder->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $this->questionBuilder->setProtocol($protocol_2)->setStatus(QuestionStatus::ACTIVE())->create();
        $this->questionBuilder->setProtocol($protocol_2)->setStatus(QuestionStatus::ACTIVE())->create();
        $this->questionBuilder->setProtocol($protocol_3)->setStatus(QuestionStatus::ACTIVE())->create();
        $this->questionBuilder->setProtocol($protocol_3)->setStatus(QuestionStatus::INACTIVE())->create();
        $this->questionBuilder->setProtocol($protocol_3)->setStatus(QuestionStatus::DRAFT())->create();

        $this->assertNull($project->start_pre_commissioning_date);
        $this->assertNull($project->end_pre_commissioning_date);
        $this->assertNull($project->start_commissioning_date);
        $this->assertNull($project->end_commissioning_date);

        $this->assertEmpty($project->protocols);

        $this->getJson(route('1c.commercial-project.start-commissioning', ['guid' => $project->guid]))
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $project->refresh();

        $this->assertNotNull($project->start_pre_commissioning_date);
        $this->assertNull($project->end_pre_commissioning_date);
        $this->assertNull($project->start_commissioning_date);
        $this->assertNull($project->end_commissioning_date);

        $protocols = Protocol::query()->get();

        $this->assertNotEmpty($project->projectProtocols);
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
        $this->assertCount(2, $project->projectProtocols[1]->projectQuestions);
        $this->assertCount(1, $project->projectProtocols[0]->projectQuestions);
    }

    /** @test */
    public function fail_project_is_start_commissioning(): void
    {
        $this->loginAsModerator();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setData([
            'start_pre_commissioning_date' => CarbonImmutable::now(),
            'guid' => $this->faker->uuid
        ])->create();

        $this->getJson(route('1c.commercial-project.start-commissioning', ['guid' => $project->guid]))
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'data' => 'This project is start pre-commissioning',
                'success' => false
            ]);
    }
}

