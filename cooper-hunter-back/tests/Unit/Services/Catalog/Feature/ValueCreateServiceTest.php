<?php

namespace Tests\Unit\Services\Catalog\Feature;

use App\Dto\Catalog\ValueDto;
use App\Models\Catalog\Features\Feature;
use App\Services\Catalog\ValueService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ValueBuilder;
use Tests\TestCase;
use Tests\Unit\Dto\Catalog\ValueDtoTest;

class ValueCreateServiceTest extends TestCase
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
    public function create(): void
    {
        $data = ValueDtoTest::data();
        $data['feature_id'] = Feature::factory()->create()->id;
        $dto = ValueDto::byArgs($data);

        $model = $this->service->create($dto);

        $this->assertEquals($model->active, $data['active']);
    }
}

