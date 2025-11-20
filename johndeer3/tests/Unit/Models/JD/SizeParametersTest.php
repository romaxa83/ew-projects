<?php

namespace Tests\Unit\Models\JD;

use App\Models\JD\SizeParameters;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SizeParametersTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_active_scope(): void
    {
        /** @var $model SizeParameters */
        $model = SizeParameters::query()->active()->first();

        $this->assertTrue($model->status);

        $model->update(["status" => false]);

        $model_1 = SizeParameters::query()->active()->first();

        $this->assertTrue($model_1->status);
        $this->assertNotEquals($model_1->id, $model->id);
    }
}
