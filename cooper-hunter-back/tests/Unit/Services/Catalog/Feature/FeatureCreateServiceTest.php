<?php

namespace Tests\Unit\Services\Catalog\Feature;;

use App\Dto\Catalog\FeatureDto;
use App\Services\Catalog\FeatureService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ValueBuilder;
use Tests\TestCase;
use Tests\Unit\Dto\Catalog\FeatureDtoTest;

class FeatureCreateServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureService $service;
    protected ValueBuilder $builderValue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FeatureService::class);
        $this->builderValue = app(ValueBuilder::class);
    }

    /** @test */
    public function create(): void
    {
        $val1 = $this->builderValue->setSort(2)->create();
        $val2 = $this->builderValue->setSort(5)->create();

        $data = FeatureDtoTest::data();
        $data['value_ids'] = [$val2->id, $val1->id];

        $dto = FeatureDto::byArgs($data);

        $model = $this->service->create($dto);

        $this->assertEquals($model->active, $data['active']);
        $this->assertNotEmpty($model->translations);

        foreach ($data['translations'] as $item) {
            $t = $model->translations->where('language', $item['language'])->first();
            $this->assertEquals($t->title, $item['title']);
            $this->assertNotNull($t->slug);
        }
    }
}

