<?php

namespace Tests\Unit\Models\Report\Feature;

use App\Models\Report\Feature\ReportFeaturePivot;
use Tests\TestCase;

class ReportFeaturePivotTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_table_name(): void
    {
        $this->assertEquals(ReportFeaturePivot::tableName(), 'reports_features_pivot');
    }
}


