<?php

namespace Tests\Feature\Modules\Catalog\V1\Product;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;

class CountTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    public function test_it_counts_purchasable_products(): void
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
        $collection = Collection::factory()->create(['published' => true]);
        $collection->products()->attach([
            $product1->id,
            $product2->id,
            $product3->id,
            $product5->id,
        ]);

        $res = $this->getJson(route('api.v1.mobile.products.count'))
            ->assertOk();

        self::assertEquals(1, $res->json('data'));
    }

    public function test_it_can_count_all_products_not_only_purchasable(): void
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

        $res = $this->getJson(route('api.v1.mobile.products.count', ['all' => true]))
            ->assertOk();

        self::assertEquals(5, $res->json('data'));
    }
}



