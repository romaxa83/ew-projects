<?php

namespace Tests\Feature\Modules\Catalog\V1\Product;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecValue;

class ProductsFilterTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    public function test_it_filters_products_by_two_specification_values_with_and_logic(): void
    {
        /** @var Specification $spec1 */
        $spec1 = Specification::factory()->create();
        /** @var SpecValue $value1 */
        $value1 = SpecValue::factory()->create(['specification_id' => $spec1->id]);
        SpecValue::factory()->count(2)->create(['specification_id' => $spec1->id]);

        /** @var Specification $spec2 */
        $spec2 = Specification::factory()->create();
        /** @var SpecValue $value2 */
        $value2 = SpecValue::factory()->create(['specification_id' => $spec2->id]);
        SpecValue::factory()->count(2)->create(['specification_id' => $spec2->id]);

        $product1 = $this->getProduct(500, 0.0);
        $product2 = $this->getProduct(1000, 0.0);
        $product3 = $this->getProduct(1500, 0.0, false);

        $product1->productSpecifications()->create([
            'spec_id' => $spec1->id,
            'spec_value_id' => $value1->id,
        ]);
        $product2->productSpecifications()->create([
            'spec_id' => $spec1->id,
            'spec_value_id' => $value1->id,
        ]);
        $product2->productSpecifications()->create([
            'spec_id' => $spec2->id,
            'spec_value_id' => $value2->id,
        ]);
        $product3->productSpecifications()->create([
            'spec_id' => $spec2->id,
            'spec_value_id' => $value2->id,
        ]);

        $filterData = [
            'specifications' => [
                $spec1->id => [$value1->id],
                $spec2->id => [$value2->id],
            ],
        ];

        $res = $this->getJson(route('api.v1.mobile.products.list', $filterData))
            ->assertOk();

        $productsData = $res->json('data');

        self::assertCount(1, $productsData);
        self::assertEquals($product2->id, $productsData[0]['id']);
    }

    public function test_it_counts_filtered_products_by_two_specification_values_with_and_logic(): void
    {
        /** @var Specification $spec1 */
        $spec1 = Specification::factory()->create();
        /** @var SpecValue $value1 */
        $value1 = SpecValue::factory()->create(['specification_id' => $spec1->id]);
        SpecValue::factory()->count(2)->create(['specification_id' => $spec1->id]);

        /** @var Specification $spec2 */
        $spec2 = Specification::factory()->create();
        /** @var SpecValue $value2 */
        $value2 = SpecValue::factory()->create(['specification_id' => $spec2->id]);
        SpecValue::factory()->count(2)->create(['specification_id' => $spec2->id]);

        $product1 = $this->getProduct(500, 0.0);
        $product2 = $this->getProduct(1000, 0.0);
        $product3 = $this->getProduct(1500, 0.0, false);

        $product1->productSpecifications()->create([
            'spec_id' => $spec1->id,
            'spec_value_id' => $value1->id,
        ]);
        $product2->productSpecifications()->create([
            'spec_id' => $spec1->id,
            'spec_value_id' => $value1->id,
        ]);
        $product2->productSpecifications()->create([
            'spec_id' => $spec2->id,
            'spec_value_id' => $value2->id,
        ]);
        $product3->productSpecifications()->create([
            'spec_id' => $spec2->id,
            'spec_value_id' => $value2->id,
        ]);

        $filterData = [
            'specifications' => [
                $spec1->id => [$value1->id],
                $spec2->id => [$value2->id],
            ],
        ];

        $res = $this->getJson(route('api.v1.mobile.products.count', $filterData))
            ->assertOk();

        self::assertEquals(1, $res->json('data'));
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



