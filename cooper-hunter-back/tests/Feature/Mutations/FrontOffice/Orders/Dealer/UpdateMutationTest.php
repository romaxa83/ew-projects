<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\DeliveryType;
use App\Enums\Orders\Dealer\OrderStatus;
use App\Enums\Orders\Dealer\OrderType;
use App\Enums\Orders\Dealer\PaymentType;
use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\UpdateMutation;
use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Payment\PaymentCardBuilder;
use Tests\TestCase;

class UpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = UpdateMutation::NAME;

    protected CompanyShippingAddressBuilder $companyShippingAddressBuilder;
    protected OrderBuilder $orderBuilder;
    protected DealerBuilder $dealerBuilder;
    protected PaymentCardBuilder $paymentCardBuilder;
    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyShippingAddressBuilder = resolve(CompanyShippingAddressBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->paymentCardBuilder = resolve(PaymentCardBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        $dealer = $this->loginAsDealerWithRole();
        $card = $this->paymentCardBuilder->create();
        $address = $this->companyShippingAddressBuilder->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'payment_type' => PaymentType::CARD(),
            'payment_card_id' => $card->id,
        ])->setDealer($dealer)->create();

        $data = $this->data();
        $data['order']['shipping_address_id'] = $address->id;
        $data['id'] = $order->id;

        $this->assertNotEquals($order->po, data_get($data, 'order.po'));
        $this->assertNotEquals($order->comment, data_get($data, 'order.comment'));
        $this->assertNotEquals($order->type, data_get($data, 'order.type'));
        $this->assertNotEquals($order->delivery_type, data_get($data, 'order.delivery_type'));
        $this->assertNotEquals($order->payment_type, data_get($data, 'order.payment_type'));
        $this->assertNotEquals($order->shipping_address_id, data_get($data, 'order.shipping_address_id'));
        $this->assertEquals($order->payment_card_id, $card->id);

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => OrderStatus::DRAFT(),
                        'type' => OrderType::STORAGE_RESPONSE(),
                        'delivery_type' => data_get($data, 'order.delivery_type'),
                        'payment_type' => data_get($data, 'order.payment_type'),
                        'po' => data_get($data, 'order.po'),
                        'comment' => data_get($data, 'order.comment'),
                        'shipping_address' => [
                            'id' => data_get($data, 'order.shipping_address_id')
                        ],
                        'payment_card' => null
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    order: {
                        delivery_type: %s
                        payment_type: %s
                        po: "%s"
                        comment: "%s"
                        shipping_address_id: %s
                        type: %s
                    }
                ) {
                    id
                    status
                    type
                    delivery_type
                    payment_type
                    po
                    comment
                    shipping_address {
                        id
                    },
                    payment_card {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'order.delivery_type'),
            data_get($data, 'order.payment_type'),
            data_get($data, 'order.po'),
            data_get($data, 'order.comment'),
            data_get($data, 'order.shipping_address_id'),
            data_get($data, 'order.type'),
        );
    }

    /** @test */
    public function update_only_po(): void
    {
        $dealer = $this->loginAsDealerWithRole();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $data = [];
        $data['order']['po'] = '8978678675675';
        $data['id'] = $order->id;

        $this->assertNotEquals($order->po, data_get($data, 'order.po'));

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPO($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => OrderStatus::DRAFT(),
                        'type' => $order->type,
                        'delivery_type' => $order->delivery_type,
                        'payment_type' => $order->payment_type,
                        'po' => data_get($data, 'order.po'),
                        'comment' => $order->comment,
                        'shipping_address' => [
                            'id' => $order->shipping_address_id
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_po_not_uniq_for_company(): void
    {
        $po = '8978678675675';
        $company = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer_1);

        /** @var $order Order */
        $order_1 = $this->orderBuilder->setDealer($dealer_1)->create();
        $order_2 = $this->orderBuilder->setData([
            'po' => $po
        ])->setDealer($dealer_2)->create();

        $data = [];
        $data['order']['po'] = $po;
        $data['id'] = $order_1->id;

        $this->assertEquals($order_2->po, $po);

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPO($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        "message" => "validation",
                        "extensions" => [
                            "validation" => [
                                "order.po" => [__('validation.unique', ['attribute' => 'order po'])]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function ignore_po_uniq_for_this_order(): void
    {
        $po = '8978678675675';
        $company = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer_2);

        /** @var $order Order */
        $order_1 = $this->orderBuilder->setDealer($dealer_1)->create();
        $order_2 = $this->orderBuilder->setData([
            'po' => $po
        ])->setDealer($dealer_2)->create();

        $data = [];
        $data['order']['po'] = $po;
        $data['id'] = $order_2->id;

        $this->assertEquals($order_2->po, $po);

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPO($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'po' => $po,
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function ignore_po_uniq_for_another_company(): void
    {
        $po = '8978678675675';
        $company_1 = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company_1)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_2)->create();

        $this->loginAsDealerWithRole($dealer_1);

        /** @var $order Order */
        $order_1 = $this->orderBuilder->setDealer($dealer_1)->create();
        $order_2 = $this->orderBuilder->setData([
            'po' => $po
        ])->setDealer($dealer_2)->create();

        $data = [];
        $data['order']['po'] = $po;
        $data['id'] = $order_1->id;

        $this->assertEquals($order_2->po, $po);

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPO($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'po' => $po,
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrOnlyPO(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    order: {
                        po: "%s"
                    }
                ) {
                    id
                    status
                    type
                    delivery_type
                    payment_type
                    po
                    comment
                    shipping_address {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'order.po'),
        );
    }

    /** @test */
    public function update_only_comment(): void
    {
        $dealer = $this->loginAsDealerWithRole();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $data = [];
        $data['order']['comment'] = '8978678675675';
        $data['id'] = $order->id;

        $this->assertNotEquals($order->comment, data_get($data, 'order.comment'));

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyComment($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => OrderStatus::DRAFT(),
                        'delivery_type' => $order->delivery_type,
                        'payment_type' => $order->payment_type,
                        'po' => $order->po,
                        'comment' => data_get($data, 'order.comment'),
                        'shipping_address' => [
                            'id' => $order->shipping_address_id
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrOnlyComment(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    order: {
                        comment: "%s"
                    }
                ) {
                    id
                    status
                    delivery_type
                    payment_type
                    po
                    comment
                    shipping_address {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'order.comment'),
        );
    }

    /** @test */
    public function update_only_type(): void
    {
        $dealer = $this->loginAsDealerWithRole();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $data = [];
        $data['order']['type'] = OrderType::STORAGE_RESPONSE();
        $data['id'] = $order->id;

        $this->assertNotEquals($order->comment, data_get($data, 'order.type'));

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyType($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => OrderStatus::DRAFT(),
                        'type' => data_get($data, 'order.type'),
                        'delivery_type' => $order->delivery_type,
                        'payment_type' => $order->payment_type,
                        'po' => $order->po,
                        'shipping_address' => [
                            'id' => $order->shipping_address_id
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrOnlyType(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    order: {
                        type: %s
                    }
                ) {
                    id
                    status
                    type
                    delivery_type
                    payment_type
                    po
                    comment
                    shipping_address {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'order.type'),
        );
    }

    /** @test */
    public function update_only_delivery_type(): void
    {
        $dealer = $this->loginAsDealerWithRole();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $data = [];
        $data['order']['delivery_type'] = DeliveryType::LTL();
        $data['id'] = $order->id;

        $this->assertNotEquals($order->delivery_type, data_get($data, 'order.delivery_type'));

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyDeliveryType($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => OrderStatus::DRAFT(),
                        'delivery_type' => data_get($data, 'order.delivery_type'),
                        'payment_type' => $order->payment_type,
                        'po' => $order->po,
                        'comment' => $order->comment,
                        'shipping_address' => [
                            'id' => $order->shipping_address_id
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrOnlyDeliveryType(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    order: {
                        delivery_type: %s
                    }
                ) {
                    id
                    status
                    delivery_type
                    payment_type
                    po
                    comment
                    shipping_address {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'order.delivery_type'),
        );
    }

    /** @test */
    public function update_only_payment_type(): void
    {
        $dealer = $this->loginAsDealerWithRole();
        $card = $this->paymentCardBuilder->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'payment_type' => PaymentType::CARD(),
            'payment_card_id' => $card->id,
        ])->setDealer($dealer)->create();

        $data = [];
        $data['order']['payment_type'] = PaymentType::BANK();
        $data['id'] = $order->id;

        $this->assertNotEquals($order->payment_type, data_get($data, 'order.payment_type'));

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPaymentType($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => OrderStatus::DRAFT(),
                        'delivery_type' => $order->delivery_type,
                        'payment_type' => data_get($data, 'order.payment_type'),
                        'po' => $order->po,
                        'comment' => $order->comment,
                        'shipping_address' => [
                            'id' => $order->shipping_address_id
                        ],
                        'payment_card' => null
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_payment_type_card_without_payment_card(): void
    {
        $dealer = $this->loginAsDealerWithRole();
        $card = $this->paymentCardBuilder->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setData([
            'payment_type' => PaymentType::CARD(),
            'payment_card_id' => $card->id,
        ])->setDealer($dealer)->create();

        $data = [];
        $data['order']['payment_type'] = PaymentType::CARD();
        $data['id'] = $order->id;

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPaymentType($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => 'validation',
                        'extensions' => [
                            'validation' => [
                                'order.payment_card_id' => ["The order.payment card id field is required when order.payment type is card."]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrOnlyPaymentType(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    order: {
                        payment_type: %s
                    }
                ) {
                    id
                    status
                    delivery_type
                    payment_type
                    po
                    comment
                    shipping_address {
                        id
                    }
                    payment_card {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'order.payment_type'),
        );
    }

    /** @test */
    public function update_only_payment_type_as_card(): void
    {
        $dealer = $this->loginAsDealerWithRole();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $card = $this->paymentCardBuilder->create();

        $data = [];
        $data['order']['payment_type'] = PaymentType::CARD();
        $data['order']['payment_card_id'] = $card->id;
        $data['id'] = $order->id;

        $this->assertNotEquals($order->payment_type, data_get($data, 'order.payment_type'));
        $this->assertNull($order->payment_card_id);

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPaymentTypeAsCard($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => OrderStatus::DRAFT(),
                        'delivery_type' => $order->delivery_type,
                        'payment_type' => data_get($data, 'order.payment_type'),
                        'po' => $order->po,
                        'comment' => $order->comment,
                        'shipping_address' => [
                            'id' => $order->shipping_address_id
                        ],
                        'payment_card' => [
                            'id' => $card->id
                        ]
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrOnlyPaymentTypeAsCard(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    order: {
                        payment_type: %s
                        payment_card_id: %s
                    }
                ) {
                    id
                    status
                    delivery_type
                    payment_type
                    po
                    comment
                    payment_card {
                        id
                    }
                    shipping_address {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'order.payment_type'),
            data_get($data, 'order.payment_card_id'),
        );
    }

    /** @test */
    public function update_only_shipping_address(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        $address = $this->companyShippingAddressBuilder->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $data = [];
        $data['order']['shipping_address_id'] = $address->id;
        $data['id'] = $order->id;

        $this->assertNotEquals($order->shipping_address_id, data_get($data, 'order.shipping_address_id'));

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyShippingAddress($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => OrderStatus::DRAFT(),
                        'delivery_type' => $order->delivery_type,
                        'payment_type' => $order->payment_type,
                        'po' => $order->po,
                        'comment' => $order->comment,
                        'shipping_address' => [
                            'id' => data_get($data, 'order.shipping_address_id')
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStrOnlyShippingAddress(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    order: {
                        shipping_address_id: %s
                    }
                ) {
                    id
                    status
                    delivery_type
                    payment_type
                    po
                    comment
                    shipping_address {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'order.shipping_address_id'),
        );
    }

    /** @test */
    public function fail_order_is_not_draft(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setStatus(OrderStatus::SENT)
            ->setDealer($dealer)->create();

        $data = [];
        $data['order']['po'] = '8978678675675';
        $data['id'] = $order->id;

        $this->assertNotEquals($order->po, data_get($data, 'order.po'));

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPO($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.order.order is not draft")]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_dealer_not_owner(): void
    {
        $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = [];
        $data['order']['po'] = '8978678675675';
        $data['id'] = $order->id;

        $this->assertNotEquals($order->po, data_get($data, 'order.po'));

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPO($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.order.can't this order")]
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

        $data = [];
        $data['order']['po'] = '8978678675675';
        $data['id'] = $order->id;

        $this->assertNotEquals($order->po, data_get($data, 'order.po'));

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPO($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.not_action_for_main")]
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = [];
        $data['order']['delivery_type'] = DeliveryType::LTL();
        $data['id'] = $order->id;

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyDeliveryType($data)
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

        $data = [];
        $data['order']['po'] = '8978678675675';
        $data['id'] = $order->id;

        $this->postGraphQL([
            'query' => $this->getQueryStrOnlyPO($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }

    public function data(): array
    {
        return [
            'order' => [
                'type' => OrderType::STORAGE_RESPONSE(),
                'delivery_type' => DeliveryType::LTL(),
                'payment_type' => PaymentType::BANK(),
                'po' => '38099990904',
                'comment' => $this->faker->sentence,
            ]
        ];
    }
}
