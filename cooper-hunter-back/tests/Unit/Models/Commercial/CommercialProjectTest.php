<?php

namespace Tests\Unit\Models\Commercial;

use App\Models\Commercial\CommercialProject;
use App\Services\Commercial\CommercialProjectService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CommercialProjectTest extends TestCase
{
    use DatabaseTransactions;

    private CommercialProjectService $service;

    public function test_address_hash(): void
    {
        $project = CommercialProject::factory()->create();

        $project->address_hash = null;

        self::assertNotNull($hash = $this->service->getAddressHash($project));

        self::assertEquals($hash, $this->service->getAddressHash($project));

        $project->zip = 'newzip';

        self::assertNotEquals($hash, $this->service->getAddressHash($project));
    }

    public function test_previous_and_next_relations(): void
    {
        $previous = CommercialProject::factory()->create();

        $project = CommercialProject::factory()
            ->state(
                [
                    'parent_id' => $previous
                ]
            )
            ->create();

        $next = CommercialProject::factory()
            ->state(
                [
                    'parent_id' => $project
                ]
            )
            ->create();

        self::assertEquals($previous->id, $project->previous->id);
        self::assertEquals($next->id, $project->next->id);

        self::assertEquals($previous->next->id, $project->id);
        self::assertEquals($next->previous->id, $project->id);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(CommercialProjectService::class);
    }
}