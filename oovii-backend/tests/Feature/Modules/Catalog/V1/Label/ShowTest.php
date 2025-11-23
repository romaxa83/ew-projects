<?php

namespace Tests\Feature\Modules\Catalog\V1\Label;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Labels\Label;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Catalog\Models\Specifications\SpecValue;

class ShowTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success(): void
    {
        Label::factory()->count(5)->create([ 'published' => true ]);

        $this->getJson(route('api.v1.mobile.products.labels.list', ['all' => true]))
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure($this->structureResource([
                '*' => [
                    'id',
                    'name',
                    'isGender',
                ]
            ]));
    }

    public function test_it_filter_labels_by_category_id(): void
    {
        $labels1 = Label::factory()->count(2)->create([ 'published' => true ]);
        $labels2 = Label::factory()->count(3)->create([ 'published' => true ]);
        /** @var Category $category1 */
        $category1 = Category::factory()->create();
        /** @var Category $category2 */
        $category2 = Category::factory()->create();
        $products1 = Product::factory()->count(3)->create(['category_id' => $category1->id]);
        $products2 = Product::factory()->count(3)->create(['category_id' => $category2->id]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['published' => true]);

        foreach ($products1 as $product) {
            $product->labels()->attach($labels1->pluck('id')->toArray());
            $product->collections()->attach($collection->id);
        }

        foreach ($products2 as $product) {
            $product->labels()->attach($labels2->pluck('id')->toArray());
            $product->collections()->attach($collection->id);
        }

        $this->getJson(route('api.v1.mobile.products.labels.list', [ 'category_id' => $category1->id ]))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_it_filter_labels_by_collection_id(): void
    {
        $labels1 = Label::factory()->count(2)->create([ 'published' => true ]);
        $labels2 = Label::factory()->count(3)->create([ 'published' => true ]);
        /** @var Collection $collection1 */
        $collection1 = Collection::factory()->create(['published' => true]);
        /** @var Collection $collection2 */
        $collection2 = Collection::factory()->create(['published' => true]);
        $products1 = Product::factory()->count(3)->create();
        $products2 = Product::factory()->count(3)->create();

        $collection1->products()->attach($products1->pluck('id')->toArray());
        $collection2->products()->attach($products2->pluck('id')->toArray());

        foreach ($products1 as $product) {
            $product->labels()->attach($labels1->pluck('id')->toArray());
        }

        foreach ($products2 as $product) {
            $product->labels()->attach($labels2->pluck('id')->toArray());
        }

        $this->getJson(route('api.v1.mobile.products.labels.list', [ 'collection_id' => $collection1->id ]))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_it_filter_labels_by_product_name(): void
    {
        /** @var Label $label1 */
        $label1 = Label::factory()->create([ 'published' => true ]);
        /** @var Label $label2 */
        $label2 = Label::factory()->create([ 'published' => true ]);

        /** @var Product $product1 */
        $product1 = Product::factory()->create([
            'ru' => ['name' => 'Холодильник'],
            'kk' => ['name' => 'Холодильник'],
        ]);
        $product1->labels()->attach($label1->id);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([
            'ru' => ['name' => 'Утюг'],
            'kk' => ['name' => 'Утюг'],
        ]);
        $product2->labels()->attach($label2->id);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['published' => true]);
        $product1->collections()->attach($collection->id);
        $product2->collections()->attach($collection->id);

        $res = $this->getJson(route('api.v1.mobile.products.labels.list', [ 'product_name' => 'холод' ]))
            ->assertOk();

        $labelsData = $res->json('data');

        self::assertCount(1, $labelsData);
        self::assertEquals($label1->id, $labelsData[0]['id']);
    }

    public function test_it_filter_labels_by_brand_id(): void
    {
        $labels1 = Label::factory()->count(1)->create([ 'published' => true ]);
        $labels2 = Label::factory()->count(2)->create([ 'published' => true ]);
        $labels3 = Label::factory()->count(3)->create([ 'published' => true ]);
        /** @var Brand $brand1 */
        $brand1 = Brand::factory()->published()->create();
        /** @var Brand $brand2 */
        $brand2 = Brand::factory()->create();
        /** @var Brand $brand3 */
        $brand3 = Brand::factory()->create();
        $products1 = Product::factory()->count(2)->create(['brand_id' => $brand1->id]);
        $products2 = Product::factory()->count(2)->create(['brand_id' => $brand2->id]);
        $products3 = Product::factory()->count(2)->create(['brand_id' => $brand3->id]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['published' => true]);

        foreach ($products1 as $product) {
            $product->labels()->attach($labels1->pluck('id')->toArray());
            $product->collections()->attach($collection->id);
        }

        foreach ($products2 as $product) {
            $product->labels()->attach($labels2->pluck('id')->toArray());
            $product->collections()->attach($collection->id);
        }

        foreach ($products3 as $product) {
            $product->labels()->attach($labels3->pluck('id')->toArray());
            $product->collections()->attach($collection->id);
        }

        $this->getJson(route('api.v1.mobile.products.labels.list', [ 'brand_id' => [$brand1->id, $brand3->id] ]))
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_it_filter_labels_by_product_cost_range(): void
    {
        /** @var Label $label1 */
        $label1 = Label::factory()->create([ 'published' => true ]);
        /** @var Label $label2 */
        $label2 = Label::factory()->create([ 'published' => true ]);
        /** @var Label $label3 */
        $label3 = Label::factory()->create([ 'published' => true ]);

        /** @var Product $product1 */
        $product1 = Product::factory()->create(['cost' => 100, 'cost_discount' => 0]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create(['cost' => 150, 'cost_discount' => 80]);
        /** @var Product $product3 */
        $product3 = Product::factory()->create(['cost' => 200, 'cost_discount' => 0]);
        /** @var Product $product4 */
        $product4 = Product::factory()->create(['cost' => 250, 'cost_discount' => 180]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['published' => true]);
        $collection->products()
            ->attach([$product1->id, $product2->id, $product3->id, $product4->id]);

        $label1->products()->attach([$product1->id, $product2->id]);
        $label2->products()->attach([$product3->id, $product4->id]);
        $label3->products()->attach([$product2->id, $product3->id]);

        $this->getJson(route('api.v1.mobile.products.labels.list', [ 'price_from' => 90, 'price_to' => 190 ]))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_it_filter_labels_by_spec_value_ids(): void
    {
        /** @var Label $label1 */
        $label1 = Label::factory()->create([ 'published' => true ]);
        /** @var Label $label2 */
        $label2 = Label::factory()->create([ 'published' => true ]);
        /** @var Label $label3 */
        $label3 = Label::factory()->create([ 'published' => true ]);

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

        /** @var Product $product1 */
        $product1 = Product::factory()->create();
        /** @var Product $product2 */
        $product2 = Product::factory()->create();
        /** @var Product $product3 */
        $product3 = Product::factory()->create();

        $label1->products()->attach([$product1->id, $product2->id]);
        $label2->products()->attach([$product2->id, $product3->id]);
        $label3->products()->attach([$product3->id, $product1->id]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['published' => true]);
        $collection->products()->attach([$product1->id, $product2->id, $product3->id]);

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

        $res = $this->getJson(route('api.v1.mobile.products.labels.list', $filterData))
            ->assertOk()
            ->assertJsonCount(2, 'data');

        self::assertEquals($label2->id, $res->json('data.0.id'));
        self::assertEquals($label1->id, $res->json('data.1.id'));
    }
}




