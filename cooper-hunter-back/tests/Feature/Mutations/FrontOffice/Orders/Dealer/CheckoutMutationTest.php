<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\Events\Orders\Dealer\CheckoutOrderEvent;
use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\CheckoutMutation;
use App\Listeners\Orders\Dealer\SendEmailToCompanyManagerListener;
use App\Models\Companies\Company;
use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\Order;
use App\Services\OneC\Client\RequestClient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Payment\PaymentCardBuilder;
use Tests\TestCase;

class CheckoutMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = CheckoutMutation::NAME;

    protected CompanyShippingAddressBuilder $companyShippingAddressBuilder;
    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected DealerBuilder $dealerBuilder;
    protected PaymentCardBuilder $paymentCardBuilder;
    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyShippingAddressBuilder = resolve(CompanyShippingAddressBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->paymentCardBuilder = resolve(PaymentCardBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_send(): void
    {
        Event::fake([CheckoutOrderEvent::class]);
        /** @var $company Company */
        $company = $this->companyBuilder->withManager()->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $dealer = $this->loginAsDealerWithRole($dealer);

        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'guid' => null,
        ])->setDealer($dealer)->setStatus(OrderStatus::DRAFT)->create();
        /** @var $item Item */
        $item = $this->itemBuilder->setOrder($order)->create();

        $res = [
            "success" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ""
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->assertNull($item->primary);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => __('messages.dealer.order.checkout.success'),
                        'type' => 'success',
                    ]
                ]
            ])
        ;

        $order->refresh();
        $item->refresh();

        $this->assertEquals($order->guid, data_get($res, 'guid'));

        $this->assertEquals($item->primary->qty, $item->qty);
        $this->assertEquals($item->primary->price, $item->price);

        Event::assertDispatched(function (CheckoutOrderEvent $event) use ($order) {
            return $event->getOrder()->id === $order->id;
        });
        Event::assertListening(CheckoutOrderEvent::class, SendEmailToCompanyManagerListener::class);
    }

    /** @test */
    public function success_send_but_error_for_create_order(): void
    {
        Event::fake([CheckoutOrderEvent::class]);
        /** @var $company Company */
        $company = $this->companyBuilder->withManager()->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $dealer = $this->loginAsDealerWithRole($dealer);

        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'guid' => null
        ])->setDealer($dealer)->create();
        /** @var $item Item */
        $item = $this->itemBuilder->setOrder($order)->create();

        $res = [
            "success" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ["some error"]
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->assertTrue($order->status->isDraft());
        $this->assertNull($item->primary);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => "some error",
                        'type' => 'warning',
                    ]
                ]
            ])
        ;

        $order->refresh();

        $this->assertEquals($order->error, data_get($res, 'error.0'));
        $this->assertNull($order->guid);
        $this->assertTrue($order->status->isDraft());
        $this->assertNull($item->primary);

        Event::assertNotDispatched(CheckoutOrderEvent::class);
    }

    /** @test */
    public function success_send_order_exist_error(): void
    {
        Event::fake([CheckoutOrderEvent::class]);
        /** @var $company Company */
        $company = $this->companyBuilder->withManager()->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $dealer = $this->loginAsDealerWithRole($dealer);

        $oldError = 'old_error';
        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'guid' => null,
            'error' => $oldError
        ])->setDealer($dealer)->create();
        /** @var $item Item */
        $item = $this->itemBuilder->setOrder($order)->create();

        $res = [
            "success" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ["some error"]
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->assertTrue($order->status->isDraft());
        $this->assertEquals($order->error, $oldError);
        $this->assertNull($item->primary);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => "some error",
                        'type' => 'warning',
                    ]
                ]
            ])
        ;

        $order->refresh();

        $this->assertEquals($order->error, data_get($res, 'error.0'));
        $this->assertNull($order->guid);
        $this->assertTrue($order->status->isDraft());
        $this->assertNull($item->primary);

        Event::assertNotDispatched(CheckoutOrderEvent::class);
    }

    /** @test */
    public function success_send_order_exist_some_errors(): void
    {
        Event::fake([CheckoutOrderEvent::class]);
        /** @var $company Company */
        $company = $this->companyBuilder->withManager()->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $dealer = $this->loginAsDealerWithRole($dealer);

        $oldError = 'old_error';
        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'guid' => null,
            'error' => $oldError
        ])->setDealer($dealer)->create();
        /** @var $item Item */
        $item = $this->itemBuilder->setOrder($order)->create();

        $res = [
            "success" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => [
                "some error 1",
                "some error 2"
            ]
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->assertTrue($order->status->isDraft());
        $this->assertEquals($order->error, $oldError);
        $this->assertNull($item->primary);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => "some error 1<br>some error 2",
                        'type' => 'warning',
                    ]
                ]
            ])
        ;

        $order->refresh();

        $this->assertEquals($order->error, implode('<br>', data_get($res, 'error')));
        $this->assertNull($order->guid);
        $this->assertTrue($order->status->isDraft());
        $this->assertNull($item->primary);

        Event::assertNotDispatched(CheckoutOrderEvent::class);
    }

    /** @test */
    public function success_send_order_exist_error_but_now_good(): void
    {
        Event::fake([CheckoutOrderEvent::class]);
        /** @var $company Company */
        $company = $this->companyBuilder->withManager()->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $dealer = $this->loginAsDealerWithRole($dealer);

        $oldError = 'old_error';
        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'guid' => null,
            'error' => $oldError
        ])->setDealer($dealer)->create();
        /** @var $item Item */
        $item = $this->itemBuilder->setOrder($order)->create();

        $res = [
            "success" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ""
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->assertTrue($order->status->isDraft());
        $this->assertEquals($order->error, $oldError);
        $this->assertNull($item->primary);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => __('messages.dealer.order.checkout.success'),
                        'type' => 'success',
                    ]
                ]
            ])
        ;

        $order->refresh();
        $item->refresh();

        $this->assertNull($order->error);
        $this->assertEquals($order->guid, data_get($res, 'guid'));
        $this->assertTrue($order->status->isSent());
        $this->assertNotNull($item->primary);

        Event::assertDispatched(function (CheckoutOrderEvent $event) use ($order) {
            return $event->getOrder()->id === $order->id;
        });
    }

    /** @test */
    public function fail_dealer_not_owner(): void
    {
        $dealer = $this->loginAsDealerWithRole();
        $dealer_1 = $this->dealerBuilder->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'guid' => null
        ])->setDealer($dealer_1)->create();
        $this->itemBuilder->setOrder($order)->create();

        $res = [
            "successful" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ""
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => __("exceptions.dealer.order.can't this order"),
                        'type' => 'warning',
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_order_already_send(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'guid' => "70751f6f-9b0d-3c5a-99fc-307d043641d5"
        ])->setDealer($dealer)->create();
        $this->itemBuilder->setOrder($order)->create();

        $res = [
            "successful" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ""
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => __('messages.dealer.order.checkout.order already sent'),
                        'type' => 'warning',
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_not_po(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'po' => null,
        ])->setDealer($dealer)->create();
        $this->itemBuilder->setOrder($order)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => __('messages.dealer.order.checkout.po not specified'),
                        'type' => 'warning',
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_not_items(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => __('messages.dealer.order.checkout.not items'),
                        'type' => 'warning',
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_update_for_main_dealer(): void
    {
        $dealer = $this->dealerBuilder->setMain()->setData([
            'is_main_company' =>false
        ])->create();
        $this->loginAsDealerWithRole($dealer);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => __("exceptions.dealer.not_action_for_main"),
                        'type' => 'warning',
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "Unauthorized"]
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsDealer();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                ) {
                    message
                    type
                }
            }',
            self::MUTATION,
            $id,
        );
    }
}
