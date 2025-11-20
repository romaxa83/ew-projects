<?php

namespace Tests\Unit\Models\Report;

use App\Models\Report\ReportClient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportClientTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_model_description_name(): void
    {
        /** @var $model ReportClient */
        $model = ReportClient::factory()->create();

        $this->assertNull($model->modelDescriptionName());
    }
}
