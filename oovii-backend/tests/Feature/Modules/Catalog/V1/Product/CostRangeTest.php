<?php

namespace Tests\Feature\Modules\Catalog\V1\Product;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;

class CostRangeTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    public function test_it_returns_products_cost_range_with_filtering_by_category_id(): void
    {
        /** @var Category $category1 */
        $category1 = Category::factory()->create();
        /** @var Category $category2 */
        $category2 = Category::factory()->create();
        $this->getProduct(100, 0, [ 'category_id' => $category1->id ]);
        $this->getProduct(300, 0, [ 'category_id' => $category1->id ]);
        $this->getProduct(200, 0, [ 'category_id' => $category2->id ]);
        $this->getProduct(400, 0, [ 'category_id' => $category2->id ]);

        $res = $this->getJson(route('api.v1.mobile.products.cost-range', [ 'category_id' => $category2->id ]))
            ->assertOk();

        self::assertEquals(200, $res->json('data.min'));
        self::assertEquals(400, $res->json('data.max'));
    }

    public function test_it_returns_products_cost_range_with_filtering_by_product_name(): void
    {
        $this->getProduct(100, 0, [
            'ru' => ['name' => 'Утюг'],
            'kk' => ['name' => 'Утюг'],
        ]);
        $this->getProduct(200, 0, [
            'ru' => ['name' => 'Холодильник'],
            'kk' => ['name' => 'Холодильник'],
        ]);
        $this->getProduct(300, 0, [
            'ru' => ['name' => 'Холодильная камера'],
            'kk' => ['name' => 'Холодильная камера'],
        ]);
        $this->getProduct(400, 0, [
            'ru' => ['name' => 'Пылесос'],
            'kk' => ['name' => 'Пылесос'],
        ]);

        $res = $this->getJson(route('api.v1.mobile.products.cost-range', [ 'search' => 'лод' ]))
            ->assertOk();

        self::assertEquals(200, $res->json('data.min'));
        self::assertEquals(300, $res->json('data.max'));
    }

    public function test_it_returns_correct_cost_range_min_with_cost_discount(): void
    {
        $this->getProduct(100, 0.0);
        $this->getProduct(200, 50.0);
        $this->getProduct(300, 250.0);
        $this->getProduct(400, 0.0);

        $res = $this->getJson(route('api.v1.mobile.products.cost-range'))
            ->assertOk();

        $range = $res->json('data');

        self::assertEquals(50, $range['min']);
        self::assertEquals(400, $range['max']);
    }

    public function test_it_returns_correct_cost_range_max_with_cost_discount(): void
    {
        $this->getProduct(100, 0.0);
        $this->getProduct(200, 0.0);
        $this->getProduct(300, 250.0);
        $this->getProduct(400, 350.0);

        $res = $this->getJson(route('api.v1.mobile.products.cost-range'))
            ->assertOk();

        $range = $res->json('data');

        self::assertEquals(100, $range['min']);
        self::assertEquals(350, $range['max']);
    }

    private function getProduct(float $cost, float $costDiscount = 0.0, array $data = []): Product
    {
        /** @var Collection $collection */
        $collection = Collection::query()->first();
        if (!$collection) {
            $collection = Collection::factory()->create(['published' => true]);
        }

        $productData = array_merge([
            'cost' => $cost,
            'cost_discount' => $costDiscount,
        ], $data);

        /** @var Product $product */
        $product = Product::factory()->create($productData);

        $product->collections()->attach($collection->id);

        return $product;
    }
}
