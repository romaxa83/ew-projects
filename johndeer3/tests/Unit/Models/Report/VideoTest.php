<?php

namespace Tests\Unit\Models\Report;

use App\Models\Report\Video;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function check_report_relation(): void
    {
        $user = $this->userBuilder->create();
        $rep = $this->reportBuilder->setUser($user)->create();

        /** @var $model Video */
        $model = Video::factory()->create([
            'report_id' => $rep->id
        ]);

        $this->assertEquals($model->report->id, $rep->id);
    }
}

