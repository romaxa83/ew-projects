<?php

namespace Tests\Feature\Http\Api\OneC\Dealers\Order;

use App\Enums\Orders\Dealer\DeliveryType;
use App\Enums\Orders\Dealer\OrderType;
use App\Enums\Orders\Dealer\PaymentType;
use App\Events\Orders\Dealer\ApprovedOrderEvent;
use App\Listeners\Orders\Dealer\SendEmailToDealerAsApprovedOrderListener;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use App\Notifications\Orders\Dealer\SendApprovedOrderToDealerNotification;
use App\Services\Orders\Dealer\OrderService;
use App\Traits\SimpleHasher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyPriceBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use SimpleHasher;
    use FakeNotifications;

    protected CompanyBuilder $companyBuilder;
    protected OrderBuilder $orderBuilder;
    protected CompanyPriceBuilder $companyPriceBuilder;
    protected DealerBuilder $dealerBuilder;
    protected ItemBuilder $itemBuilder;
    protected ProductBuilder $productBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;

    protected array $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->companyPriceBuilder = resolve(CompanyPriceBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->productBuilder = resolve(productBuilder::class);
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);

        $this->data = [
            'guid' => $this->faker->uuid,
            'po' => $this->faker->postcode,
            'delivery_type' => DeliveryType::TRUCK,
            'payment_type' => PaymentType::BANK,
            'type' => OrderType::ORDINARY,
            'comment' => $this->faker->sentence,
            'term' => $this->faker->sentence,
            'tax' => 10,
            'shipping_price' => 10.5,
            'total' => 11,
            'total_discount' => 11.5,
            'total_with_discount' => 12,
        ];
    }

    /** @test */
    public function success_create(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder
            ->setCompany($company)->create();
        $dealer_1 = $this->dealerBuilder->setCompany($company)->setData([
            'is_main_company' => false
        ])->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_1)->create();
        $price_2 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_2)->create();
        $price_3 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_3)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $data = $this->data;
        $data['shipping_address_id'] = $address->id;
        $data['company_guid'] = $company->guid;
        $data['term'] = $this->faker->sentence;
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'discount' => 6,
                'discount_total' => 6.5,
                'total' => 7,
                'price' => 7.5,
                'description' => $this->faker->sentence,
                'qty' => 3,
            ],
            [
                'guid' => $product_2->guid,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => $this->faker->sentence,
                'qty' => 4,
            ],
            [
                'guid' => $product_3->guid,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
                'qty' => 1,
            ]
        ];

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model = Order::query()->where('guid', data_get($data, 'guid'))->first();

        $this->assertTrue($model->status->isApproved());
        $this->assertEquals($model->dealer_id, $dealer->id);
        $this->assertEquals($model->po, data_get($data, 'po'));
        $this->assertEquals($model->delivery_type, data_get($data, 'delivery_type'));
        $this->assertEquals($model->payment_type, data_get($data, 'payment_type'));
        $this->assertEquals($model->type, data_get($data, 'type'));
        $this->assertEquals($model->comment, data_get($data, 'comment'));
        $this->assertEquals($model->terms, data_get($data, 'term'));
        $this->assertEquals($model->shipping_address_id, data_get($data, 'shipping_address_id'));

        $this->assertNotNull($model->created_at);
        $this->assertNotNull($model->updated_at);

        $this->assertNull($model->tracking_number);
        $this->assertNull($model->tracking_company);
        $this->assertNull($model->shipped_at);
        $this->assertNull($model->payment_card_id);
        $this->assertNull($model->files);
        $this->assertNull($model->invoice);
        $this->assertNull($model->invoice_at);
        $this->assertNull($model->error);
        $this->assertNotNull($model->approved_at);
        $this->assertNotNull($model->hash);

        $this->assertEquals($model->tax, data_get($data, 'tax'));
        $this->assertEquals($model->shipping_price, data_get($data, 'shipping_price'));
        $this->assertEquals($model->total, data_get($data, 'total'));
        $this->assertEquals($model->total_discount, data_get($data, 'total_discount'));
        $this->assertEquals($model->total_with_discount, data_get($data, 'total_with_discount'));

        $this->assertEquals($model->items[0]->product_id, $product_1->id);
        $this->assertEquals($model->items[0]->price, data_get($data, 'products.0.price'));
        $this->assertEquals($model->items[0]->discount, data_get($data, 'products.0.discount'));
        $this->assertEquals($model->items[0]->discount_total, data_get($data, 'products.0.discount_total'));
        $this->assertEquals($model->items[0]->total, data_get($data, 'products.0.total'));
        $this->assertEquals($model->items[0]->description, data_get($data, 'products.0.description'));
        $this->assertEquals($model->items[0]->qty, data_get($data, 'products.0.qty'));
        $this->assertEquals($model->items[0]->primary->price, data_get($data, 'products.0.price'));
        $this->assertEquals($model->items[0]->primary->qty, data_get($data, 'products.0.qty'));

        $this->assertEquals($model->items[1]->product_id, $product_2->id);
        $this->assertEquals($model->items[1]->price, data_get($data, 'products.1.price'));
        $this->assertEquals($model->items[1]->discount, data_get($data, 'products.1.discount'));
        $this->assertEquals($model->items[1]->discount_total, data_get($data, 'products.1.discount_total'));
        $this->assertEquals($model->items[1]->total, data_get($data, 'products.1.total'));
        $this->assertEquals($model->items[1]->description, data_get($data, 'products.1.description'));
        $this->assertEquals($model->items[1]->qty, data_get($data, 'products.1.qty'));
        $this->assertEquals($model->items[1]->primary->price, data_get($data, 'products.1.price'));
        $this->assertEquals($model->items[1]->primary->qty, data_get($data, 'products.1.qty'));

        $this->assertEquals($model->items[2]->product_id, $product_3->id);
        $this->assertEquals($model->items[2]->price, data_get($data, 'products.2.price'));
        $this->assertEquals($model->items[2]->discount, data_get($data, 'products.2.discount'));
        $this->assertEquals($model->items[2]->discount_total, data_get($data, 'products.2.discount_total'));
        $this->assertEquals($model->items[2]->total, data_get($data, 'products.2.total'));
        $this->assertEquals($model->items[2]->description, data_get($data, 'products.2.description'));
        $this->assertEquals($model->items[2]->qty, data_get($data, 'products.2.qty'));
        $this->assertEquals($model->items[2]->primary->price, data_get($data, 'products.2.price'));
        $this->assertEquals($model->items[2]->primary->qty, data_get($data, 'products.2.qty'));

        Event::assertDispatched(function (ApprovedOrderEvent $event) use ($model) {
            return $event->getOrder()->id === $model->id;
        });
        Event::assertListening(ApprovedOrderEvent::class, SendEmailToDealerAsApprovedOrderListener::class);
    }

    /** @test */
    public function success_create_only_required_fields(): void
    {
        Notification::fake();

        $this->loginAsModerator();

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $product_1 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_1)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $data = $this->data;
        $data['shipping_address_id'] = $address->id;
        $data['company_guid'] = $company->guid;
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'discount' => 6,
                'total' => 7,
                'price' => 7.5,
                'qty' => 3,
            ],
        ];
        unset(
            $data['comment'],
            $data['term'],
            $data['tax'],
            $data['shipping_price'],
            $data['total'],
            $data['total_discount'],
            $data['total_with_discount'],
        );

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model = Order::query()->where('guid', data_get($data, 'guid'))->first();

        $this->assertTrue($model->status->isApproved());
        $this->assertEquals($model->dealer_id, $dealer->id);
        $this->assertEquals($model->po, data_get($data, 'po'));
        $this->assertEquals($model->delivery_type, data_get($data, 'delivery_type'));
        $this->assertEquals($model->payment_type, data_get($data, 'payment_type'));
        $this->assertEquals($model->type, OrderType::ORDINARY());
        $this->assertNull($model->comment);
        $this->assertNull($model->terms);

        $this->assertNull($model->tracking_number);
        $this->assertNull($model->tracking_company);
        $this->assertNull($model->shipped_at);
        $this->assertNull($model->payment_card_id);
        $this->assertNull($model->files);
        $this->assertNull($model->invoice);
        $this->assertNull($model->invoice_at);
        $this->assertNull($model->error);
        $this->assertNotNull($model->approved_at);
        $this->assertNotNull($model->hash);

        $this->assertEquals($model->tax, 0);
        $this->assertEquals($model->shipping_price, 0);
        $this->assertEquals($model->total, 0);
        $this->assertEquals($model->total_discount, 0);
        $this->assertEquals($model->total_with_discount, 0);

        $this->assertEquals($model->items[0]->product_id, $product_1->id);
        $this->assertEquals($model->items[0]->price, data_get($data, 'products.0.price'));
        $this->assertEquals($model->items[0]->discount, data_get($data, 'products.0.discount'));
        $this->assertEquals($model->items[0]->discount_total, 0);
        $this->assertEquals($model->items[0]->total, data_get($data, 'products.0.total'));
        $this->assertNull($model->items[0]->description);
        $this->assertEquals($model->items[0]->qty, data_get($data, 'products.0.qty'));
        $this->assertEquals($model->items[0]->primary->price, data_get($data, 'products.0.price'));
        $this->assertEquals($model->items[0]->primary->qty, data_get($data, 'products.0.qty'));

        $this->assertNotificationSentTo(
            $dealer->email->getValue(),
            SendApprovedOrderToDealerNotification::class
        );
    }

    /** @test */
    public function fail_not_found_address(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $address = $this->addressBuilder->create();

        $data = $this->data;
        $data['company_guid'] = $company->guid;
        $data['shipping_address_id'] = $address->id;
        $guid = $this->faker->uuid;
        $data['products'] = [
            [
                'guid' => $guid,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
                'qty' => 1,
            ]
        ];

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertJson([
                'data' => "This company [{$dealer->company->business_name}] does not have this shipping address",
                'success' => false
            ]);

        Event::assertNotDispatched(ApprovedOrderEvent::class);
    }

    /** @test */
    public function fail_not_found_product(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $data = $this->data;
        $data['company_guid'] = $company->guid;
        $data['shipping_address_id'] = $address->id;
        $guid = $this->faker->uuid;
        $data['products'] = [
            [
                'guid' => $guid,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
                'qty' => 1,
            ]
        ];

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertJson([
                'data' => "Product not found by guide [{$guid}]",
                'success' => false
            ]);

        Event::assertNotDispatched(ApprovedOrderEvent::class);
    }

    /** @test */
    public function fail_not_found_price(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $product_1 = $this->productBuilder->create();

        $data = $this->data;
        $data['company_guid'] = $company->guid;
        $data['shipping_address_id'] = $address->id;
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
                'qty' => 1,
            ]
        ];

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertJson([
                'data' => "Price not found by company [{$dealer->company->business_name}] fo this product [$product_1->guid]",
                'success' => false
            ]);

        Event::assertNotDispatched(ApprovedOrderEvent::class);
    }

    /** @test */
    public function fail_not_found_company(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $product_1 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_1)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $data = $this->data;
        $data['company_guid'] = $this->faker->uuid;
        $data['shipping_address_id'] = $address->id;
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
                'qty' => 3,
            ],
        ];

        $res = $this->postJson(route('1c.dealer-order.create'), $data)
        ;

        $this->assertEquals($res->json('errors.0.messages.0'), 'The selected company guid is invalid.');

        Event::assertNotDispatched(ApprovedOrderEvent::class);
    }

    /** @test */
    public function fail_company_not_have_main_dealer(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->setData([
            'is_main_company' => false
        ])->create();

        $product_1 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_1)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $data = $this->data;
        $data['company_guid'] = $company->guid;
        $data['shipping_address_id'] = $address->id;
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
                'qty' => 3,
            ],
        ];

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertJson([
                'data' => "Сan't find the main dealer in the company [{$company->guid}]",
                'success' => false
            ]);

        Event::assertNotDispatched(ApprovedOrderEvent::class);
    }

    /** @test */
    public function fail_wrong_payment_type(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $product_1 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_1)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $data = $this->data;
        $data['company_guid'] = $company->guid;
        $data['shipping_address_id'] = $address->id;
        $data['payment_type'] = 'wrong';
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 3,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
            ],
        ];

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertJson([
                'data' => "Cannot construct an instance of PaymentType using the value (string) `wrong`. Possible values are [none, card, paypal, wiredTransfer, check, flooring].",
                'success' => false
            ]);

        Event::assertNotDispatched(ApprovedOrderEvent::class);
    }

    /** @test */
    public function fail_wrong_delivery_type(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $product_1 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_1)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $data = $this->data;
        $data['company_guid'] = $company->guid;
        $data['shipping_address_id'] = $address->id;
        $data['delivery_type'] = 'wrong';
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 3,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
            ],
        ];

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertJson([
                'data' => "Cannot construct an instance of DeliveryType using the value (string) `wrong`. Possible values are [none, ltl, pickUp, train, truck].",
                'success' => false
            ]);

        Event::assertNotDispatched(ApprovedOrderEvent::class);
    }

    /** @test */
    public function fail_po_not_uniq(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        $po = $this->faker->postcode;

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $order = $this->orderBuilder->setDealer($dealer)->setData([
            'po' => $po
        ])->create();

        $product_1 = $this->productBuilder->create();
        $price_1 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_1)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $data = $this->data;
        $data['company_guid'] = $company->guid;
        $data['shipping_address_id'] = $address->id;
        $data['po'] = $po;
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 3,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
            ],
        ];

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertJson([
                'data' => "PO [$po] is not unique",
                'success' => false
            ]);

        Event::assertNotDispatched(ApprovedOrderEvent::class);
    }

    /** @test */
    public function fail_po_not_uniq_another_dealer(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        $po = $this->faker->postcode;

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        $dealer_1 = $this->dealerBuilder->setCompany($company)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $order = $this->orderBuilder->setDealer($dealer_1)->setData([
            'po' => $po
        ])->create();

        $product_1 = $this->productBuilder->create();
        $price_1 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_1)->create();

        $data = $this->data;
        $data['company_guid'] = $company->guid;
        $data['po'] = $po;
        $data['shipping_address_id'] = $address->id;
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 3,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
            ],
        ];

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertJson([
                'data' => "PO [$po] is not unique",
                'success' => false
            ]);

        Event::assertNotDispatched(ApprovedOrderEvent::class);
    }

    /** @test */
    public function success_po_not_uniq_another_company(): void
    {
        $this->loginAsModerator();

        $po = $this->faker->postcode;
        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        $company_1 = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        $dealer_1 = $this->dealerBuilder->setCompany($company_1)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $order = $this->orderBuilder->setDealer($dealer_1)->setData([
            'po' => $po
        ])->create();

        $product_1 = $this->productBuilder->create();
        $price_1 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_1)->create();

        $data = $this->data;
        $data['company_guid'] = $company->guid;
        $data['po'] = $po;
        $data['shipping_address_id'] = $address->id;
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 3,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
            ],
        ];

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertJson([
                'data' => "Done",
                'success' => true,
            ]);
    }

    /** @test */
    public function fail_something_wrong_to_service(): void
    {
        Event::fake([ApprovedOrderEvent::class]);

        $this->loginAsModerator();

        /** @var $company Company */
        $company = $this->companyBuilder->withGuid()->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $product_1 = $this->productBuilder->create();
        $price_1 = $this->companyPriceBuilder->setCompany($company)->setProduct($product_1)->create();

        $address = $this->addressBuilder->setCompany($company)->create();

        $data = $this->data;
        $data['company_guid'] = $company->guid;
        $data['shipping_address_id'] = $address->id;
        $data['products'] = [
            [
                'guid' => $product_1->guid,
                'qty' => 3,
                'discount' => 16,
                'discount_total' => 16.5,
                'total' => 17,
                'price' => 17.5,
                'description' => null,
            ],
        ];

        $this->mock(OrderService::class, function(MockInterface $mock){
            $mock->shouldReceive("createOnec")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('1c.dealer-order.create'), $data)
            ->assertStatus(500)
            ->assertJson([
                'data' => "some exception message",
                'success' => false
            ]);

        Event::assertNotDispatched(ApprovedOrderEvent::class);
    }
}
