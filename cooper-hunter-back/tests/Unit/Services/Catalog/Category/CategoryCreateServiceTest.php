<?php

namespace Tests\Unit\Services\Catalog\Category;

use App\Dto\Catalog\CategoryDto;
use App\Services\Catalog\Categories\CategoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\CategoryBuilder;
use Tests\TestCase;
use Tests\Unit\Dto\Catalog\CategoryDtoTest;

class CategoryCreateServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryService $service;
    protected CategoryBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CategoryService::class);
        $this->builder = app(CategoryBuilder::class);
    }

    /** @test */
    public function create(): void
    {
        $data = CategoryDtoTest::data();
        $data['parent_id'] = null;

        $dto = CategoryDto::byArgs($data);

        $model = $this->service->create($dto);

        $this->assertEquals($model->active, $data['active']);
        $this->assertEquals($model->parent_id, $data['parent_id']);
        $this->assertEquals($model->slug, $data['slug']);
        $this->assertNotEmpty($model->translations);

        foreach ($data['translations'] as $item) {
            $t = $model->translations->where('language', $item['language'])->first();
            $this->assertEquals($t->title, $item['title']);
        }
    }

    /** @test */
    public function create_with_parent(): void
    {
        $parent = $this->builder->create();

        $data = CategoryDtoTest::data();
        $data['parent_id'] = $parent->id;

        $dto = CategoryDto::byArgs($data);

        $model = $this->service->create($dto);

        $this->assertEquals($model->parent_id, $parent->id);
    }
}
