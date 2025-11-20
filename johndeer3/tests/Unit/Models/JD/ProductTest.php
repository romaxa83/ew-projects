<?php

namespace Tests\Unit\Models\JD;

use App\Models\JD\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_active_scope(): void
    {
        /** @var $model Product */
        $model = Product::query()->active()->first();

        $this->assertTrue($model->status);

        $model->update(["status" => false]);

        $model_1 = Product::query()->active()->first();

        $this->assertTrue($model_1->status);
        $this->assertNotEquals($model_1->id, $model->id);
    }
}

