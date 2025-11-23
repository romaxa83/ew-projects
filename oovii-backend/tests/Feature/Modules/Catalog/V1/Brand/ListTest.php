<?php

namespace Tests\Feature\Modules\Catalog\V1\Brand;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;

class ListTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    public function test_it_can_filter_brands_by_category_id(): void
    {
        $brands1 = Brand::factory()->count(3)->create([ 'published' => true ]);
        $brands2 = Brand::factory()->count(5)->create([ 'published' => true ]);

        /** @var Category $category1 */
        $category1 = Category::factory()->create();
        /** @var Category $category2 */
        $category2 = Category::factory()->create();

        $products1 = Product::factory()
            ->count(3)
            ->sequence(fn ($sequence) => [ 'brand_id' => $brands1->get($sequence->index) ])
            ->create([ 'category_id' => $category1->id ]);
        $products2 = Product::factory()
            ->count(5)
            ->sequence(fn ($sequence) => [ 'brand_id' => $brands2->get($sequence->index) ])
            ->create([ 'category_id' => $category2->id ]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['published' => true]);
        $collection->products()->attach($products1->pluck('id')->toArray());
        $collection->products()->attach($products2->pluck('id')->toArray());

        $res = $this->getJson(route('api.v1.mobile.brands', [ 'category_id' => $category1->id ]))
            ->assertOk();

        self::assertCount(3, $res->json('data'));

        $brandsIds = $brands1->pluck('id')->toArray();
        foreach ($res->json('data') as $brandData) {
            self::assertContains($brandData['id'], $brandsIds);
        }
    }

    public function test_it_can_filter_brands_by_product_name(): void
    {
        /** @var Brand $brand1 */
        $brand1 = Brand::factory()->create([ 'published' => true ]);
        /** @var Brand $brand2 */
        $brand2 = Brand::factory()->create([ 'published' => true ]);

        /** @var Product $product1 */
        $product1 = Product::factory()->create([
            'brand_id' => $brand1->id,
            'ru' => ['name' => 'Холодильник'],
            'kk' => ['name' => 'Холодильник'],
        ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([
            'brand_id' => $brand2->id,
            'ru' => ['name' => 'Утюг'],
            'kk' => ['name' => 'Утюг'],
        ]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['published' => true]);

        $product1->collections()->attach($collection->id);
        $product2->collections()->attach($collection->id);

        $res = $this->getJson(route('api.v1.mobile.brands', [ 'product_name' => 'диль' ]))
            ->assertOk();

        $brandsData = $res->json('data');

        self::assertCount(1, $brandsData);
        self::assertEquals($brand1->id, $brandsData[0]['id']);
    }

    public function test_it_can_find_brands_only_by_products_from_active_collections(): void
    {
        /** @var Brand $brand1 */
        $brand1 = Brand::factory()->create([ 'published' => true ]);
        /** @var Brand $brand2 */
        $brand2 = Brand::factory()->create([ 'published' => true ]);

        Product::factory()->create([
            'brand_id' => $brand1->id,
            'ru' => ['name' => 'Холодильник'],
            'kk' => ['name' => 'Холодильник'],
            'amount' => 0,
        ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([
            'brand_id' => $brand2->id,
            'ru' => ['name' => 'Холодильник'],
            'kk' => ['name' => 'Холодильник'],
        ]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create([
            'published' => true,
            'start_at' => Carbon::now()->subWeeks(3),
            'end_at' => Carbon::now()->subWeeks(1),
        ]);

        $product2->collections()->attach($collection->id);

        $this->getJson(route('api.v1.mobile.brands', [ 'product_name' => 'диль' ]))
            ->assertJsonCount(0, 'data');
    }

    public function test_it_can_get_all_brands(): void
    {
        /** @var Brand $brand1 */
        Brand::factory()->count(5)->create([ 'published' => true ]);

        $this->getJson(route('api.v1.mobile.brands', [ 'all' => true ]))
            ->assertJsonCount(5, 'data');
    }
}



