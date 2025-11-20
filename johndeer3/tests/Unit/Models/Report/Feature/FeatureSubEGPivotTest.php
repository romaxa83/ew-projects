<?php

namespace Tests\Unit\Models\Report\Feature;

use App\Models\Report\Feature\FeatureSubEGPivot;
use Tests\TestCase;

class FeatureSubEGPivotTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_table_name(): void
    {
        $this->assertEquals(FeatureSubEGPivot::tableName(), 'report_features_sub_eg_pivot');
    }
}

