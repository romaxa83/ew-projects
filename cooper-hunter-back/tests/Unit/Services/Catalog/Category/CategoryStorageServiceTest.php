<?php

namespace Tests\Unit\Services\Catalog\Category;

use App\Entities\Catalog\Categories\CategorySimple;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\Catalog\Products\Product;
use App\Services\Catalog\Categories\CategoryStorageService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CategoryStorageServiceTest extends TestCase
{
    use DatabaseTransactions;

    private CategoryStorageService $service;

    public function test_it_create_categories_tree(): void
    {
        $categoryRoot1 = Category::factory()->create();
        $categoryRoot2 = Category::factory()->create();

        Category::factory()->create(['parent_id' => $categoryRoot1->id]);
        Category::factory()->create(['parent_id' => $categoryRoot1->id]);

        Category::factory()->create(['parent_id' => $categoryRoot2->id]);

        $storage = $this->service->generate();

        $tree = $storage->getTree();
        self::assertInstanceOf(Collection::class, $tree);

        $this->assertCount(2, $tree);

        $category = $tree->shift();
        self::assertInstanceOf(CategorySimple::class, $category);
        $this->assertCount(2, $category->getChildren());

        $category = $tree->shift();
        self::assertInstanceOf(CategorySimple::class, $category);
        $this->assertCount(1, $category->getChildren());
    }

    /**
     * @throws Exception
     */
    public function test_it_get_all_parents(): void
    {
        $root1 = Category::factory()->create();
        $root2 = Category::factory()->create();

        $middle1 = Category::factory()->create(['parent_id' => $root1->id]);
        $middle2 = Category::factory()->create(['parent_id' => $root2->id]);

        $leaf1 = Category::factory()->create(['parent_id' => $middle1->id]);
        $leaf2 = Category::factory()->create(['parent_id' => $middle2->id]);

        $storage = $this->service->generate();

        $parents = $storage->findParentsChain($leaf1->id);
        $this->assertCount(2, $parents);

        $parents = $storage->findParentsChain($leaf2->id);
        $this->assertCount(2, $parents);

        $parents = $storage->findParentsChain($middle2->id);
        $this->assertCount(1, $parents);
    }

    /**
     * @throws Exception
     */
    public function test_it_get_all_children(): void
    {
        $root1 = Category::factory()->create();
        $root2 = Category::factory()->create();

        $middle1 = Category::factory()->create(['parent_id' => $root1->id]);
        $middle1_1 = Category::factory()->create(['parent_id' => $root1->id]);
        $middle2 = Category::factory()->create(['parent_id' => $root2->id]);

        $leaf1 = Category::factory()->create(['parent_id' => $middle1->id]);
        $leaf2 = Category::factory()->create(['parent_id' => $middle2->id]);

        $storage = $this->service->generate();

        self::assertEquals(
            [$root1->id, $middle1->id, $leaf1->id, $middle1_1->id],
            $storage->getAllChildrenIds($root1)
        );

        self::assertEquals(
            [$root2->id, $middle2->id, $leaf2->id],
            $storage->getAllChildrenIds($root2)
        );
    }

    /**
     * @throws Exception
     */
    public function test_it_get_all_parents_for_all_category_ids(): void
    {
        $root1 = Category::factory()->create();
        $root2 = Category::factory()->create();

        $middle1 = Category::factory()->create(['parent_id' => $root1->id]);
        $middle2 = Category::factory()->create(['parent_id' => $root2->id]);

        $leaf1 = Category::factory()->create(['parent_id' => $middle1->id]);
        $leaf2 = Category::factory()->create(['parent_id' => $middle2->id]);

        $storage = $this->service->generate();

        $result = $storage->findParentIdsForAll([$root1->id, $leaf2->id]);

        $this->assertCount(
            0,
            array_diff(
                [$root1->id, $root2->id, $middle2->id, $leaf2->id],
                $result
            )
        );
    }

    public function test_calculate_products_for_root_category(): void
    {
        $root1 = Category::factory()->create();
        $root2 = Category::factory()->create();

        $child1 = Category::factory()
            ->for($root1, 'parent')
            ->create();

        $child2 = Category::factory()
            ->for($child1, 'parent')
            ->create();

        $child3 = Category::factory()
            ->for($child2, 'parent')
            ->create();

        $child4 = Category::factory()
            ->for($child2, 'parent')
            ->create();

        Product::factory()
            ->times(5)
            ->for($child3)
            ->create();

        Product::factory()
            ->times(2)
            ->for($child4)
            ->create();

        Product::factory()
            ->times(3)
            ->for($root2)
            ->create();

        self::assertEquals(7, $this->service->getTotalCountForCategory($root1));
    }

    public function test_zero_products_for_empty_category(): void
    {
        $category = Category::factory()->create();
        self::assertEquals(0, $this->service->getTotalCountForCategory($category));

        $category = Category::factory()->make();
        self::assertEquals(0, $this->service->getTotalCountForCategory($category));
    }

    /**
     * @throws Exception
     */
    public function test_build_breadcrumbs_for_category(): void
    {
        $c = Category::factory()
            ->has(CategoryTranslation::factory()->allLocales(), 'translations')
            ->for(
                Category::factory()
                    ->for(
                        Category::factory()
                            ->has(CategoryTranslation::factory()->allLocales(), 'translations'),
                        'parent'
                    )
                    ->has(CategoryTranslation::factory()->allLocales(), 'translations'),
                'parent'
            )
            ->create();

        $crumbs = $this->service->generate()->buildBreadcrumbs($c);

        self::assertCount(3, $crumbs);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Category::query()->delete();

        $this->service = resolve(CategoryStorageService::class)->clear();
    }
}
