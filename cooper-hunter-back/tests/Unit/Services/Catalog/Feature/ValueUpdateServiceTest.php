<?php

namespace Tests\Unit\Services\Catalog\Feature;

use App\Dto\Catalog\ValueDto;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Services\Catalog\ValueService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ValueBuilder;
use Tests\TestCase;
use Tests\Unit\Dto\Catalog\ValueDtoTest;

class ValueUpdateServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected ValueService $service;
    protected ValueBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ValueService::class);
        $this->builder = app(ValueBuilder::class);
    }

    /** @test */
    public function update(): void
    {
        $model = $this->builder->create();

        $data = ValueDtoTest::data();
        $data['feature_id'] = Feature::factory()->create()->id;

        $this->assertNotEquals($model->sort, $data['sort']);
        $this->assertNotEquals($model->active, $data['active']);
        $this->assertNotEquals(
            $model->title,
            $data['title']
        );

        $dto = ValueDto::byArgs($data);
        $this->service->update($dto, $model);

        $model->refresh();

        $this->assertEquals($model->active, $data['active']);
        $this->assertEquals(
            $model->title,
            $data['title']
        );
    }

    /** @test */
    public function toggleActive(): void
    {
        $model = $this->builder->create();

        $this->assertTrue($model->active);

        $this->service->toggleActive($model);

        $this->assertFalse($model->active);

        $this->service->toggleActive($model);

        $this->assertTrue($model->active);
    }

    /** @test */
    public function remove(): void
    {
        $model = $this->builder->create();
        $id = $model->id;

        $this->service->delete($model);

        $m = Value::find($id);

        $this->assertNull($m);
    }
}

