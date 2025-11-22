<?php

namespace Tests\Feature\Http\Api\OneC\Commercial\Project;

use App\Models\Commercial\CommercialProject;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\Builders\Commercial\ProjectUnitBuilder;
use Tests\TestCase;

class RemoveUnitTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected $projectBuilder;
    protected $projectUnitBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->projectUnitBuilder = resolve(ProjectUnitBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $this->loginAsModerator();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $data = [
            'data' => [
                '00001', '00002', '00003',
            ]
        ];

        $this->projectUnitBuilder->setProject($project)
            ->setSerialNumber(data_get($data, 'data.0'))->create();
        $this->projectUnitBuilder->setProject($project)
            ->setSerialNumber(data_get($data, 'data.1'))->create();
        $this->projectUnitBuilder->setProject($project)
            ->setSerialNumber(data_get($data, 'data.2'))->create();

        $project->refresh();

        $this->assertCount(3, $project->units);

        $this->postJson(route('1c.commercial-project.remove-units', ['guid' => $project->guid]), [
            'data' => [data_get($data, 'data.0'), data_get($data, 'data.1')]
        ])
            ->assertOk()
            ->assertJson([
                'data' => 'Delete - [2], records',
                'success' => true
            ]);

        $project->refresh();

        $this->assertCount(1, $project->units);
        $this->assertEquals($project->units[0]->serial_number, data_get($data, 'data.2'));
    }

    /** @test */
    public function success_empty_data(): void
    {
        $this->loginAsModerator();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $data = [
            'data' => [
                '00001', '00002', '00003',
            ]
        ];

        $this->projectUnitBuilder->setProject($project)
            ->setSerialNumber(data_get($data, 'data.0'))->create();
        $this->projectUnitBuilder->setProject($project)
            ->setSerialNumber(data_get($data, 'data.1'))->create();
        $this->projectUnitBuilder->setProject($project)
            ->setSerialNumber(data_get($data, 'data.2'))->create();

        $project->refresh();

        $this->assertCount(3, $project->units);

        $this->postJson(route('1c.commercial-project.remove-units', ['guid' => $project->guid]), [])
            ->assertOk()
            ->assertJson([
                'data' => 'Delete - [0], records',
                'success' => true
            ]);

        $project->refresh();

        $this->assertCount(3, $project->units);
    }

    /** @test */
    public function success_not_unit(): void
    {
        $this->loginAsModerator();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->projectUnitBuilder->setProject($project)
            ->setSerialNumber('00001')->create();

        $project->refresh();

        $this->assertCount(1, $project->units);

        $this->postJson(route('1c.commercial-project.remove-units', ['guid' => $project->guid]), [
            'data' => ['00002']
        ])
            ->assertOk()
            ->assertJson([
                'data' => 'Delete - [0], records',
                'success' => true
            ]);

        $project->refresh();

        $this->assertCount(1, $project->units);
    }
}

