<?php

namespace Tests\Feature\Modules\Catalog\V1\Product;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
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

    public function test_it_doesnt_include_products_without_collection_to_list(): void
    {
        $this->getProduct(700, 0.0);
        $this->getProduct(600, 400.0);
        $this->getProduct(500, 0.0, false);
        $this->getProduct(900, 0.0);
        $this->getProduct(800, 650.0, false);

        $res = $this->getJson(route('api.v1.mobile.products.list'))
            ->assertOk();

        $productsData = $res->json('data');

        self::assertCount(3, $productsData);
    }

    public function test_it_can_order_products_by_cost_desc(): void
    {
        $product1 = $this->getProduct(700, 0.0);
        $product2 = $this->getProduct(600, 400.0);
        $product3 = $this->getProduct(500, 0.0);
        $product4 = $this->getProduct(900, 0.0);
        $product5 = $this->getProduct(800, 650.0);

        $res = $this->getJson(route('api.v1.mobile.products.list', [ 'order_by' => 'cost' ]))
            ->assertOk();

        $productsData = $res->json('data');

        self::assertEquals($product4->id, $productsData[0]['id']);
        self::assertEquals($product5->id, $productsData[1]['id']);
        self::assertEquals($product1->id, $productsData[2]['id']);
        self::assertEquals($product2->id, $productsData[3]['id']);
        self::assertEquals($product3->id, $productsData[4]['id']);
    }

    public function test_it_can_order_products_by_price_desc(): void
    {
        $product1 = $this->getProduct(700, 0.0);
        $product2 = $this->getProduct(600, 400.0);
        $product3 = $this->getProduct(500, 0.0);
        $product4 = $this->getProduct(900, 0.0);
        $product5 = $this->getProduct(800, 650.0);

        $res = $this->getJson(route('api.v1.mobile.products.list', [ 'order_by' => 'price' ]))
            ->assertOk();

        $productsData = $res->json('data');

        self::assertEquals($product4->id, $productsData[0]['id']);
        self::assertEquals($product1->id, $productsData[1]['id']);
        self::assertEquals($product5->id, $productsData[2]['id']);
        self::assertEquals($product3->id, $productsData[3]['id']);
        self::assertEquals($product2->id, $productsData[4]['id']);
    }

    public function test_it_can_order_products_by_price_asc(): void
    {
        $product1 = $this->getProduct(700, 0.0);
        $product2 = $this->getProduct(600, 400.0);
        $product3 = $this->getProduct(500, 0.0);
        $product4 = $this->getProduct(900, 0.0);
        $product5 = $this->getProduct(800, 650.0);

        $res = $this->getJson(route('api.v1.mobile.products.list', [ 'order_by' => 'price', 'order_type' => 'asc' ]))
            ->assertOk();

        $productsData = $res->json('data');

        self::assertEquals($product2->id, $productsData[0]['id']);
        self::assertEquals($product3->id, $productsData[1]['id']);
        self::assertEquals($product5->id, $productsData[2]['id']);
        self::assertEquals($product1->id, $productsData[3]['id']);
        self::assertEquals($product4->id, $productsData[4]['id']);
    }

    public function test_it_can_filter_products_by_min_price(): void
    {
        $product1 = $this->getProduct(700, 0.0);
        $product2 = $this->getProduct(600, 400.0);
        $product3 = $this->getProduct(500, 0.0);
        $product4 = $this->getProduct(900, 0.0);
        $product5 = $this->getProduct(800, 650.0);

        $res = $this->getJson(route('api.v1.mobile.products.list', [ 'priceFrom' => 550, 'order_by' => 'price' ]))
            ->assertOk();

        $productsData = $res->json('data');

        self::assertCount(3, $productsData);
        self::assertEquals($product4->id, $productsData[0]['id']);
        self::assertEquals($product1->id, $productsData[1]['id']);
        self::assertEquals($product5->id, $productsData[2]['id']);
    }

    public function test_it_can_filter_products_by_max_price(): void
    {
        $product1 = $this->getProduct(700, 0.0);
        $product2 = $this->getProduct(600, 400.0);
        $product3 = $this->getProduct(500, 0.0);
        $product4 = $this->getProduct(900, 0.0);
        $product5 = $this->getProduct(800, 650.0);

        $res = $this->getJson(route('api.v1.mobile.products.list', [ 'priceTo' => 700, 'order_by' => 'price' ]))
            ->assertOk();

        $productsData = $res->json('data');

        self::assertCount(4, $productsData);
        self::assertEquals($product1->id, $productsData[0]['id']);
        self::assertEquals($product5->id, $productsData[1]['id']);
        self::assertEquals($product3->id, $productsData[2]['id']);
        self::assertEquals($product2->id, $productsData[3]['id']);
    }

    public function test_it_can_filter_products_by_min_and_max_price(): void
    {
        $product1 = $this->getProduct(700, 0.0);
        $product2 = $this->getProduct(600, 400.0);
        $product3 = $this->getProduct(500, 0.0);
        $product4 = $this->getProduct(900, 0.0);
        $product5 = $this->getProduct(800, 650.0);

        $res = $this->getJson(route('api.v1.mobile.products.list', [ 'priceFrom' => 500, 'priceTo' => 700, 'order_by' => 'price' ]))
            ->assertOk();

        $productsData = $res->json('data');

        self::assertCount(3, $productsData);
        self::assertEquals($product1->id, $productsData[0]['id']);
        self::assertEquals($product5->id, $productsData[1]['id']);
        self::assertEquals($product3->id, $productsData[2]['id']);
    }

    public function test_it_returns_only_unexpired_products(): void
    {
        /** @var Product $product1 */
        $product1 = Product::factory()->create(['expires_at' => now()->addDay()]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create(['expires_at' => now()]);
        /** @var Product $product3 */
        $product3 = Product::factory()->create(['expires_at' => now()->subDay()]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create([
            'published' => true,
            'end_at' => now()->addDays(5),
        ]);

        $collection->products()->attach([$product1->id, $product2->id, $product3->id]);

        $res = $this->getJson(route('api.v1.mobile.products.list', [ 'order_by' => 'id', 'order_type' => 'asc' ]))
            ->assertOk();

        $products = $res->json('data');
        self::assertCount(2, $products);
        self::assertEquals($product1->id, $products[0]['id']);
        self::assertEquals($product2->id, $products[1]['id']);
    }

    public function test_it_can_returns_all_products_not_only_purchasable(): void
    {
        /** @var Product $product1 unavailable product*/
        $product1 = Product::factory()->create(['available' => false]);
        /** @var Product $product2 product without stock*/
        $product2 = Product::factory()->create(['amount' => 0]);
        /** @var Product $product3 expired product */
        $product3 = Product::factory()->create(['expires_at' => Carbon::now()->subDay()]);
        /** @var Product $product4 product without collection*/
        $product4 = Product::factory()->create([]);
        /** @var Product $product5 available product*/
        $product5 = Product::factory()->create([]);
        /** @var Collection $collection */
        $collection = Collection::factory()->create();
        $collection->products()->attach([
            $product1->id,
            $product2->id,
            $product3->id,
            $product5->id,
        ]);

        $res = $this->getJson(route('api.v1.mobile.products.list', [ 'order_by' => 'id', 'order_type' => 'asc', 'all' => true ]))
            ->assertOk();

        $products = $res->json('data');
        self::assertCount(5, $products);
        self::assertEquals($product1->id, $products[0]['id']);
        self::assertEquals($product2->id, $products[1]['id']);
        self::assertEquals($product3->id, $products[2]['id']);
        self::assertEquals($product4->id, $products[3]['id']);
        self::assertEquals($product5->id, $products[4]['id']);
    }

    private function getProduct(float $cost, float $costDiscount = 0.0, bool $inCollection = true): Product
    {
        /** @var Product $product */
        $product = Product::factory()->create([
            'cost' => $cost,
            'cost_discount' => $costDiscount,
        ]);

        if ($inCollection) {
            $collection = Collection::first();

            if (!$collection) {
                $collection = Collection::factory()->create([
                    'published' => true,
                    'end_at' => now()->addDays(5),
                ]);
            }

            $collection->products()->attach($product->id);
        }

        return $product;
    }
}



