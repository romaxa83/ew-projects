<?php

namespace Tests\Unit\Services\Catalog\Product;

use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Metric;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Products\Product;
use App\Services\Catalog\ProductService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductSimilarTest extends TestCase
{
    use DatabaseTransactions;

    private Collection $values;

    private Product $product;

    private ProductService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->values = Value::factory()
            ->for(
                Feature::factory()
                    ->web()
                    ->create()
            )
            ->has(
                Metric::factory()
            )
            ->count(3)
            ->create();

        $this->product = Product::factory()
            ->hasAttached(
                $this->values
            )
            ->create();

        $this->service = resolve(ProductService::class);
    }

    public function test_get_similar_product(): void
    {
        $products = Product::factory(['category_id' => $this->product->category_id])
            ->hasAttached($this->values)
            ->count(2)
            ->create();

        $similar = $this->service->getSimilarProducts($this->product);

        $this->assertNotNull($similar);
        $this->assertCount(2, $similar);

        $this->assertTrue(
            $products->pluck('id')
                ->diff($similar->pluck('id'))
                ->isEmpty()
        );
    }

    public function test_get_part_similar_product(): void
    {
        $product1 = Product::factory(['category_id' => $this->product->category_id])
            ->hasAttached(
                $this->values[1]
            )
            ->hasAttached(
                $this->values[2]
            )
            ->create();

        $product2 = Product::factory(['category_id' => $this->product->category_id])
            ->hasAttached(
                $this->values[1]
            )
            ->hasAttached(
                Value::factory()
                    ->for(
                        Feature::factory()
                            ->web()
                            ->create()
                    )
                    ->has(
                        Metric::factory()
                    )
                    ->create()
            )
            ->hasAttached(
                $this->values[2]
            )
            ->create();

        $product3 = Product::factory(['category_id' => $this->product->category_id])
            ->hasAttached($this->values)
            ->create();

        $similar = $this->service->getSimilarProducts($this->product);

        $this->assertNotNull($similar);
        $this->assertCount(3, $similar);

        $this->assertEquals(
            $similar->pluck('id')
                ->toArray(),
            [
                $product1->id,
                $product2->id,
                $product3->id,
            ]
        );
    }
}
