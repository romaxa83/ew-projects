<?php

namespace Tests\Feature\Http\Api\OneC\Dealers\Order;

use App\Enums\Orders\Dealer\OrderStatus;
use App\Events\Orders\Dealer\ApprovedOrderEvent;
use App\Listeners\Orders\Dealer\SendEmailToDealerAsApprovedOrderListener;
use App\Models\Orders\Dealer\Order;
use App\Services\Orders\Dealer\OrderService;
use App\Traits\SimpleHasher;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use SimpleHasher;

    protected OrderBuilder $orderBuilder;
    protected CompanyBuilder $companyBuilder;
    protected DealerBuilder $dealerBuilder;
    protected ItemBuilder $itemBuilder;
    protected ProductBuilder $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->productBuilder = resolve(productBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->assertTrue($model->status->isDraft());

        $data = [
            'status' => OrderStatus::SENT,
        ];

        $this->postJson(route('1c.dealer-order.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertTrue($model->status->isSent());
    }

    /** @test */
    public function success_update_status_approve(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->assertTrue($model->status->isDraft());

        $data = [
            'status' => OrderStatus::APPROVED,
        ];

        $this->assertNull($model->approved_at);

        $this->postJson(route('1c.dealer-order.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertTrue($model->status->isApproved());
        $this->assertNotNull($model->approved_at);

        Event::assertDispatched(function (ApprovedOrderEvent $event) use ($model) {
            return $event->getOrder()->id === $model->id;
        });
        Event::assertListening(ApprovedOrderEvent::class, SendEmailToDealerAsApprovedOrderListener::class);
    }

    /** @test */
    public function not_approved_at_if_it_exist(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        $approvedAt = CarbonImmutable::now()->subDay();
        /** @var $model Order */
        $model = $this->orderBuilder->setData([
            'status' => OrderStatus::APPROVED,
            'approved_at' => $approvedAt
        ])->create();

        $this->assertTrue($model->status->isApproved());

        $data = [
            'status' => OrderStatus::APPROVED,
        ];

        $this->postJson(route('1c.dealer-order.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertTrue($model->status->isApproved());
        $this->assertEquals($model->approved_at->format('Y-m-d H:i:s'), $approvedAt->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function update_prices(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->assertTrue($model->status->isDraft());

        $data = [
            'tax' => 20.5,
            'shipping_price' => 50,
            'total' => 1000.5,
            'total_discount' => 10,
            'total_with_discount' => 200,
        ];

        $this->assertNotEquals($model->tax, data_get($data, 'tax'));
        $this->assertNotEquals($model->shipping_price, data_get($data, 'shipping_price'));
        $this->assertNotEquals($model->total, data_get($data, 'total'));
        $this->assertNotEquals($model->total_discount, data_get($data, 'total_discount'));
        $this->assertNotEquals($model->total_with_discount, data_get($data, 'total_with_discount'));

        $this->postJson(route('1c.dealer-order.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->tax, data_get($data, 'tax'));
        $this->assertEquals($model->shipping_price, data_get($data, 'shipping_price'));
        $this->assertEquals($model->total, data_get($data, 'total'));
        $this->assertEquals($model->total_discount, data_get($data, 'total_discount'));
        $this->assertEquals($model->total_with_discount, data_get($data, 'total_with_discount'));
    }

    /** @test */
    public function update_term(): void
    {
        $this->loginAsModerator();

        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $model Order */
        $model = $this->orderBuilder->setDealer($dealer)->create();

        $this->assertEmpty($model->terms);

        $data = [
            'term' => $this->faker->sentence,
        ];

        $this->postJson(route('1c.dealer-order.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->terms, data_get($data, 'term'));
    }

    /** @test */
    public function update_items(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $model Order */
        $model = $this->orderBuilder->setDealer($dealer)->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($model)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setOrder($model)->create();
        $item_3 = $this->itemBuilder->setProduct($product_3)->setOrder($model)->create();

        $data = [
            'status' => OrderStatus::APPROVED,
            'products' => [
                [
                    'guid' => $product_1->guid,
                    'discount' => 101.0,
                    'discount_total' => 201.0,
                    'qty' => 15,
                    'total' => 505.0,
                    'price' => 25.6,
                    'description' => $this->faker->sentence,
                ],
                [
                    'guid' => $product_2->guid,
                    'discount' => 10.0,
                    'discount_total' => 301.0,
                    'qty' => 18,
                    'total' => 80,
                    'price' => 8,
                    'description' => $this->faker->sentence,
                ],
                [
                    'guid' => $product_3->guid,
                    'discount' => 1,
                    'qty' => 11,
                    'total' => 1,
                    'price' => 12,
                    'description' => $this->faker->sentence,
                ]
            ]
        ];

        $this->assertCount(3, $model->items);
        $this->assertNull($model->hash);

        $this->assertEquals($model->items[0]->id, $item_1->id);
        $this->assertNotEquals($model->items[0]->discount, data_get($data, 'products.0.discount'));
        $this->assertNotEquals($model->items[0]->discount_total, data_get($data, 'products.0.discount_total'));
        $this->assertNotEquals($model->items[0]->qty, data_get($data, 'products.0.qty'));
        $this->assertNotEquals($model->items[0]->total, data_get($data, 'products.0.total'));
        $this->assertNotEquals($model->items[0]->price, data_get($data, 'products.0.price'));
        $this->assertNotEquals($model->items[0]->description, data_get($data, 'products.0.description'));

        $this->assertEquals($model->items[1]->id, $item_2->id);
        $this->assertNotEquals($model->items[1]->discount, data_get($data, 'products.1.discount'));
        $this->assertNotEquals($model->items[1]->discount_total, data_get($data, 'products.1.discount_total'));
        $this->assertNotEquals($model->items[1]->qty, data_get($data, 'products.1.qty'));
        $this->assertNotEquals($model->items[1]->total, data_get($data, 'products.1.total'));
        $this->assertNotEquals($model->items[1]->price, data_get($data, 'products.1.price'));
        $this->assertNotEquals($model->items[1]->description, data_get($data, 'products.1.description'));

        $this->assertEquals($model->items[2]->id, $item_3->id);
        $this->assertNotEquals($model->items[2]->discount, data_get($data, 'products.2.discount'));
        $this->assertNotEquals($model->items[2]->discount_total, data_get($data, 'products.2.discount_total', 0));
        $this->assertNotEquals($model->items[2]->qty, data_get($data, 'products.2.qty'));
        $this->assertNotEquals($model->items[2]->total, data_get($data, 'products.2.total'));
        $this->assertNotEquals($model->items[2]->price, data_get($data, 'products.2.price'));
        $this->assertNotEquals($model->items[2]->description, data_get($data, 'products.2.description'));

        $this->postJson(route('1c.dealer-order.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->hash, self::hash($data['products']));
        $this->assertTrue($model->equalsHash(self::hash($data['products'])));
        $this->assertCount(3, $model->items);

        $this->assertEquals($model->items[0]->id, $item_1->id);
        $this->assertEquals($model->items[0]->discount, data_get($data, 'products.0.discount'));
        $this->assertEquals($model->items[0]->discount_total, data_get($data, 'products.0.discount_total'));
        $this->assertEquals($model->items[0]->qty, data_get($data, 'products.0.qty'));
        $this->assertEquals($model->items[0]->total, data_get($data, 'products.0.total'));
        $this->assertEquals($model->items[0]->price, data_get($data, 'products.0.price'));
        $this->assertEquals($model->items[0]->description, data_get($data, 'products.0.description'));

        $this->assertEquals($model->items[1]->id, $item_2->id);
        $this->assertEquals($model->items[1]->discount, data_get($data, 'products.1.discount'));
        $this->assertEquals($model->items[1]->discount_total, data_get($data, 'products.1.discount_total'));
        $this->assertEquals($model->items[1]->qty, data_get($data, 'products.1.qty'));
        $this->assertEquals($model->items[1]->total, data_get($data, 'products.1.total'));
        $this->assertEquals($model->items[1]->price, data_get($data, 'products.1.price'));
        $this->assertEquals($model->items[1]->description, data_get($data, 'products.1.description'));

        $this->assertEquals($model->items[2]->id, $item_3->id);
        $this->assertEquals($model->items[2]->discount, data_get($data, 'products.2.discount'));
        $this->assertEquals($model->items[2]->discount_total, 0);
        $this->assertEquals($model->items[2]->qty, data_get($data, 'products.2.qty'));
        $this->assertEquals($model->items[2]->total, data_get($data, 'products.2.total'));
        $this->assertEquals($model->items[2]->price, data_get($data, 'products.2.price'));
        $this->assertEquals($model->items[2]->description, data_get($data, 'products.2.description'));

        Event::assertDispatched(function (ApprovedOrderEvent $event) use ($model) {
            return $event->getOrder()->id === $model->id;
        });
        Event::assertListening(ApprovedOrderEvent::class, SendEmailToDealerAsApprovedOrderListener::class);
    }

    /** @test */
    public function update_items_not_sent_approved_email(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $model Order */
        $model = $this->orderBuilder->setDealer($dealer)->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($model)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setOrder($model)->create();

        $data = [
            'status' => OrderStatus::APPROVED,
            'products' => [
                [
                    'guid' => $product_1->guid,
                    'discount' => $item_1->discount,
                    'qty' => $item_1->qty,
                    'total' => $item_1->total,
                    'price' => $item_1->price,
                    'description' => $item_1->description,
                ],
                [
                    'guid' => $product_2->guid,
                    'discount' => $item_2->discount,
                    'qty' => $item_2->qty,
                    'total' => $item_2->total,
                    'price' => $item_2->price,
                    'description' => $item_2->description,
                ]
            ]
        ];

        $model->update(['hash' => self::hash($data['products'])]);

        $this->postJson(route('1c.dealer-order.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->hash, self::hash($data['products']));
        $this->assertTrue($model->equalsHash(self::hash($data['products'])));

        Event::assertNotDispatched(ApprovedOrderEvent::class);
        Event::assertListening(ApprovedOrderEvent::class, SendEmailToDealerAsApprovedOrderListener::class);
    }

    /** @test */
    public function remove_items(): void
    {
        $this->loginAsModerator();

        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $model Order */
        $model = $this->orderBuilder->setDealer($dealer)->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($model)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setOrder($model)->create();
        $item_3 = $this->itemBuilder->setProduct($product_3)->setOrder($model)->create();

        $data = [
            'products' => [
                [
                    'guid' => $product_1->guid,
                    'discount' => 100.0,
                    'qty' => 8,
                    'total' => 800.0,
                    'price' => 8,
                ]
            ]
        ];

        $this->assertCount(3, $model->items);

        $this->postJson(route('1c.dealer-order.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertCount(1, $model->items);

        $this->assertEquals($model->items[0]->id, $item_1->id);
        $this->assertEquals($model->items[0]->discount, data_get($data, 'products.0.discount'));
        $this->assertEquals($model->items[0]->qty, data_get($data, 'products.0.qty'));
        $this->assertEquals($model->items[0]->total, data_get($data, 'products.0.total'));
        $this->assertEquals($model->items[0]->price, data_get($data, 'products.0.price'));
    }

    /** @test */
    public function fail_not_found_item(): void
    {
        $this->loginAsModerator();

        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $model Order */
        $model = $this->orderBuilder->setDealer($dealer)->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $item_1 = $this->itemBuilder->setProduct($product_1)->setOrder($model)->create();
        $item_2 = $this->itemBuilder->setProduct($product_2)->setOrder($model)->create();
        $item_3 = $this->itemBuilder->setProduct($product_3)->setOrder($model)->create();

        $guid = $this->faker->uuid;
        $data = [
            'products' => [
                [
                    'guid' => $product_1->guid,
                    'discount' => 100,
                    'qty' => 8,
                    'total' => 800,
                    'price' => 1,
                ],
                [
                    'guid' => $guid,
                    'discount' => 100,
                    'qty' => 8,
                    'total' => 800,
                    'price' => 2,
                ],
                [
                    'guid' => $product_3->guid,
                    'discount' => 0,
                    'qty' => 0,
                    'total' => 0,
                    'price' => 3,
                ]
            ]
        ];

        $this->assertCount(3, $model->items);

        $this->assertEquals($model->items[0]->id, $item_1->id);
        $this->assertNotEquals($model->items[0]->discount, data_get($data, 'products.0.discount'));
        $this->assertNotEquals($model->items[0]->qty, data_get($data, 'products.0.qty'));

        $this->assertEquals($model->items[1]->id, $item_2->id);
        $this->assertNotEquals($model->items[1]->discount, data_get($data, 'products.1.discount'));
        $this->assertNotEquals($model->items[1]->qty, data_get($data, 'products.1.qty'));

        $this->postJson(route('1c.dealer-order.update', ['guid' => $model->guid]), $data)
            ->assertJson([
                'data' => 'There is no item in the order by guid - '. $guid,
                'success' => false
            ]);

        $model->refresh();

        $this->assertCount(3, $model->items);

        $this->assertEquals($model->items[0]->id, $item_1->id);
        $this->assertNotEquals($model->items[0]->discount, data_get($data, 'products.0.discount'));
        $this->assertNotEquals($model->items[0]->qty, data_get($data, 'products.0.qty'));

        $this->assertEquals($model->items[1]->id, $item_2->id);
        $this->assertNotEquals($model->items[1]->discount, data_get($data, 'products.1.discount'));
        $this->assertNotEquals($model->items[1]->qty, data_get($data, 'products.1.qty'));
    }

    /** @test */
    public function fail_not_order(): void
    {
        $this->loginAsModerator();

        $guid = '342342423';
        $this->postJson(route('1c.dealer-order.update', ['guid' => $guid]), [])
            ->assertStatus(404)
            ->assertJson([
                'data' => __('exceptions.dealer.order.not found by guid' , ['guid' => $guid]),
                'success' => false
            ]);
    }

    /** @test */
    public function fail_something_wrong_to_service(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->mock(OrderService::class, function(MockInterface $mock){
            $mock->shouldReceive("updateOnec")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('1c.dealer-order.update', ['guid' => $model->guid]), [])
            ->assertStatus(500)
            ->assertJson([
                'data' => "some exception message",
                'success' => false
            ]);
    }
}
