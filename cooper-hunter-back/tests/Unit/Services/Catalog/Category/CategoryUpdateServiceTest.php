<?php

namespace Tests\Unit\Services\Catalog\Category;

use App\Dto\Catalog\CategoryDto;
use App\Models\Catalog\Categories\Category;
use App\Services\Catalog\Categories\CategoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\CategoryBuilder;
use Tests\TestCase;
use Tests\Unit\Dto\Catalog\CategoryDtoTest;

class CategoryUpdateServiceTest extends TestCase
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
    public function update(): void
    {
        $categoryParent = $this->builder->create();
        $category = $this->builder->withTranslation()->create();

        $data = CategoryDtoTest::data();
        $data['parent_id'] = $categoryParent->id;

        $this->assertNotEquals($category->active, $data['active']);
        $this->assertNotEquals($category->parent_id, $data['parent_id']);
        $this->assertNotEquals(
            $category->translation->title,
            $data['translations'][$category->translation->language]['title']
        );
        $this->assertNotEquals(
            $category->translation->description,
            $data['translations'][$category->translation->language]['description']
        );

        $dto = CategoryDto::byArgs($data);
        $this->service->update($dto, $category);

        $category->refresh();

        $this->assertEquals($category->active, $data['active']);
        $this->assertEquals($category->parent_id, $data['parent_id']);
        $this->assertEquals(
            $category->translation->title,
            $data['translations'][$category->translation->language]['title']
        );
        $this->assertEquals(
            $category->translation->description,
            $data['translations'][$category->translation->language]['description']
        );
    }

    /** @test */
    public function toggleActive(): void
    {
        $category = $this->builder->create();

        $this->assertTrue($category->active);

        $this->service->toggleActive($category);

        $this->assertFalse($category->active);

        $this->service->toggleActive($category);

        $this->assertTrue($category->active);
    }

    /** @test */
    public function remove(): void
    {
        $category = $this->builder->create();
        $id = $category->id;

        $this->service->delete($category);

        $cat = Category::find($id);

        $this->assertNull($cat);
    }
}

