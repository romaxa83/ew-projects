<?php

namespace Tests\Feature\Modules\Catalog\V1\Specifications;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecValue;

class ListTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    public function test_it_can_filter_specifications_by_category_id(): void
    {
        $spec1 = Specification::factory()->count(3)->create();
        $values1 = SpecValue::factory()
            ->count(3)
            ->sequence(fn ($sequence) => [ 'specification_id' => $spec1->get($sequence->index) ])
            ->create();
        $spec2 = Specification::factory()->count(5)->create();
        $values2 = SpecValue::factory()
            ->count(5)
            ->sequence(fn ($sequence) => [ 'specification_id' => $spec2->get($sequence->index) ])
            ->create();

        /** @var Category $category1 */
        $category1 = Category::factory()->create();
        /** @var Category $category2 */
        $category2 = Category::factory()->create();

        $products1 = Product::factory()
            ->count(3)
            ->create([ 'category_id' => $category1->id ]);
        $products2 = Product::factory()
            ->count(5)
            ->create([ 'category_id' => $category2->id ]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['published' => true]);
        $collection->products()->attach($products1->pluck('id')->toArray());
        $collection->products()->attach($products2->pluck('id')->toArray());

        $products1->each(function (Product $product, int $index) use ($spec1, $values1) {
            $product->productSpecifications()->create([
                'spec_id' => $spec1->get($index)->id,
                'spec_value_id' => $values1->get($index)->id,
            ]);
        });
        $products2->each(function (Product $product, int $index) use ($spec2, $values2) {
            $product->productSpecifications()->create([
                'spec_id' => $spec2->get($index)->id,
                'spec_value_id' => $values2->get($index)->id,
            ]);
        });

        $res = $this->getJson(route('api.v1.mobile.specifications', [ 'category_id' => $category1->id ]))
            ->assertOk();

        self::assertCount(3, $res->json('data'));

        $specIds = $spec1->pluck('id')->toArray();
        foreach ($res->json('data') as $specData) {
            self::assertContains($specData['id'], $specIds);
        }
    }

    public function test_it_can_filter_specifications_by_product_name(): void
    {
        /** @var Specification $spec1 */
        $spec1 = Specification::factory()->create();
        /** @var SpecValue $value1 */
        $value1 = SpecValue::factory()->create(['specification_id' => $spec1->id]);
        /** @var Specification $spec2 */
        $spec2 = Specification::factory()->create();
        /** @var SpecValue $value2 */
        $value2 = SpecValue::factory()->create(['specification_id' => $spec2->id]);

        /** @var Product $product1 */
        $product1 = Product::factory()->create([
            'ru' => ['name' => 'Холодильник'],
            'kk' => ['name' => 'Холодильник'],
        ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([
            'ru' => ['name' => 'Утюг'],
            'kk' => ['name' => 'Утюг'],
       ]);

        $product1->productSpecifications()->create([
            'spec_id' => $spec1->id,
            'spec_value_id' => $value1->id,
        ]);
        $product2->productSpecifications()->create([
            'spec_id' => $spec2->id,
            'spec_value_id' => $value2->id,
        ]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['published' => true]);
        $product1->collections()->attach($collection->id);
        $product2->collections()->attach($collection->id);

        $res = $this->getJson(route('api.v1.mobile.specifications', [ 'product_name' => 'ильн' ]))
            ->assertOk();

        $specData = $res->json('data');

        self::assertCount(1, $specData);
        self::assertEquals($spec1->id, $specData[0]['id']);
    }

    public function test_it_can_get_all_specifications(): void
    {
        $specs = Specification::factory()->count(5)->create();
        SpecValue::factory()
            ->count(5)
            ->sequence(fn ($sequence) => [ 'specification_id' => $specs->get($sequence->index) ])
            ->create();

        $this->getJson(route('api.v1.mobile.specifications', [ 'all' => true ]))
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }
}



