<?php

namespace Tests\Feature\Modules\Catalog\V1\Collection;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\Builders\CollectionsBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;

class ListTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    private $collectionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->collectionBuilder = app(CollectionsBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        // check
        $collection1 = $this->collectionBuilder
            ->setPublished(true)
            ->setStartAt(Carbon::now()->subDay())
            ->setEndAt(Carbon::now()->addDay())
            ->create();
        // check, without products
        $this->collectionBuilder
            ->setPublished(true)
            ->setStartAt(Carbon::now()->subDay())
            ->setEndAt(Carbon::now()->addDay())
            ->create();
        //not check
        $collection3 = $this->collectionBuilder
            ->setPublished(false)
            ->setStartAt(Carbon::now()->subDay())
            ->setEndAt(Carbon::now()->addDay())
            ->create();
        // not check
        $collection4 = $this->collectionBuilder
            ->setPublished(true)
            ->setStartAt(Carbon::now()->subDay())
            ->setEndAt(Carbon::now()->subMinute())
            ->create();
        // check
        $collection5 = $this->collectionBuilder
            ->setPublished(true)
            ->setStartAt(Carbon::now()->addHour())
            ->setEndAt(Carbon::now()->addDay())
            ->create();

        /** @var Product $product */
        $product = Product::factory()->create();
        $product->collections()->attach([
            $collection1->id,
            $collection3->id,
            $collection4->id,
            $collection5->id,
        ]);

        $res = $this->getJson(route('api.v1.mobile.collections'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure($this->structureResource([
                '*' => [
                    'id',
                    'name',
                    'image',
                    'startAt',
                    'endAt',
                    'createdAt',
                ]
            ]));

        $temp = [];
        array_map(static function($item) use (&$temp){
            $temp[$item['id']] = [
                'name' => $item['name'],
                'startAt' => $item['startAt'],
                'endAt' => $item['endAt'],
                'image' => $item['image']
            ];
        }, $res->json('data'));

        self::assertEquals(
            $collection1->start_at->format(config('cms.core.time.format.start_at.api')),
            $temp[$collection1->id]['startAt']
        );
        self::assertEquals(
            $collection1->end_at->format(config('cms.core.time.format.end_at.api')),
            $temp[$collection1->id]['endAt']
        );
        self::assertEquals($collection1->name, $temp[$collection1->id]['name']);

        self::assertEquals(
            $collection5->start_at->format(config('cms.core.time.format.start_at.api')),
            $temp[$collection5->id]['startAt']
        );
        self::assertEquals(
            $collection5->end_at->format(config('cms.core.time.format.end_at.api')),
            $temp[$collection5->id]['endAt']
        );
        self::assertEquals($collection5->name, $temp[$collection5->id]['name']);
    }

    public function test_it_returns_only_purchasable_products_in_list(): void
    {
        /** @var Collection $collection */
        $collection = Collection::factory()->create();
        /** @var Product $product1 */
        $product1 = Product::factory()->create([
            'available' => false,
        ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([
            'available' => true,
        ]);
        /** @var Product $product3 */
        $product3 = Product::factory()->create([
            'available' => true,
            'amount' => 0,
        ]);
        /** @var Product $product4 */
        $product4 = Product::factory()->create([
            'available' => true,
            'published' => false,
        ]);
        /** @var Product $product5 */
        $product5 = Product::factory()->create([
            'available' => true,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $collection->products()->attach([$product1->id, $product2->id, $product3->id, $product4->id, $product5->id]);

        $res = $this->getJson(route('api.v1.mobile.collections.show', [ 'id' => $collection->id ]))
            ->assertOk();

        self::assertCount(1, $res->json('data.products'));
        self::assertEquals($product2->id, $res->json('data.products.0.id'));
    }
}



