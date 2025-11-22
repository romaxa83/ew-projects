<?php

namespace Tests\Feature\Http\Api\OneC\Dealers\Order;

use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use App\Services\Orders\Dealer\PackingSlipService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipItemBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipSerialNumberBuilder;
use Tests\TestCase;

class AddSerialNumberToPackingSlipTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected OrderBuilder $orderBuilder;
    protected CompanyBuilder $companyBuilder;
    protected DealerBuilder $dealerBuilder;
    protected PackingSlipSerialNumberBuilder $serialNumberBuilder;
    protected ProductBuilder $productBuilder;
    protected PackingSlipBuilder $packingSlipBuilder;
    protected PackingSlipItemBuilder $packingSlipItemBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->serialNumberBuilder = resolve(PackingSlipSerialNumberBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->packingSlipBuilder = resolve(PackingSlipBuilder::class);
        $this->packingSlipItemBuilder = resolve(PackingSlipItemBuilder::class);
    }

    /** @test */
    public function add_serial_numbers(): void
    {
        $this->loginAsModerator();

        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        /** @var $packing_slip PackingSlip */
        $packing_slip = $this->packingSlipBuilder->setOrder($order)->create();

        $packing_slip_item_1 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip)
            ->setProduct($product_1)->create();
        $packing_slip_item_2 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip)
            ->setProduct($product_2)->create();
        $packing_slip_item_3 = $this->packingSlipItemBuilder->setPackingSlip($packing_slip)
            ->setProduct($product_3)->create();

        $this->assertEmpty($packing_slip->serialNumbers);

        $data = [
            'data' => [
                [
                    'guid' => $product_1->guid,
                    'serial_numbers' => [
                        $this->faker->creditCardNumber,
                        $this->faker->creditCardNumber,
                        $this->faker->creditCardNumber,
                    ]
                ],
                [
                    'guid' => $product_2->guid,
                    'serial_numbers' => [
                        $this->faker->creditCardNumber,
                        $this->faker->creditCardNumber,
                    ]
                ]
            ]
        ];

        $this->postJson(route('1c.dealer-order.packing-slip.add-serial-number', ['guid' => $packing_slip->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => [],
                'success' => true
            ]);

        $packing_slip->refresh();

        $this->assertCount(3, $packing_slip->serialNumbers->where('product_id', $product_1->id));
        $this->assertCount(2, $packing_slip->serialNumbers->where('product_id', $product_2->id));

        $this->assertEquals(
            $packing_slip->serialNumbers->where('product_id', $product_1->id)[0]->serial_number,
            data_get($data, 'data.0.serial_numbers.0')
        );
        $this->assertEquals(
            $packing_slip->serialNumbers->where('product_id', $product_1->id)[1]->serial_number,
            data_get($data, 'data.0.serial_numbers.1')
        );
        $this->assertEquals(
            $packing_slip->serialNumbers->where('product_id', $product_1->id)[2]->serial_number,
            data_get($data, 'data.0.serial_numbers.2')
        );

        $this->assertEquals(
            $packing_slip->serialNumbers->where('product_id', $product_2->id)[3]->serial_number,
            data_get($data, 'data.1.serial_numbers.0')
        );
        $this->assertEquals(
            $packing_slip->serialNumbers->where('product_id', $product_2->id)[4]->serial_number,
            data_get($data, 'data.1.serial_numbers.1')
        );
    }

    /** @test */
    public function delete_serial_numbers(): void
    {
        $this->loginAsModerator();

        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        /** @var $packing_slip PackingSlip */
        $packing_slip = $this->packingSlipBuilder->setOrder($order)->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $packing_slip_item_1 = $this->packingSlipItemBuilder->setProduct($product_1)
            ->setPackingSlip($packing_slip)->create();
        $packing_slip_item_2 = $this->packingSlipItemBuilder->setProduct($product_2)
            ->setPackingSlip($packing_slip)->create();
        $packing_slip_item_3 = $this->packingSlipItemBuilder->setProduct($product_3)
            ->setPackingSlip($packing_slip)->create();

        $serial_number_1 = $this->serialNumberBuilder->setProduct($product_1)
            ->setPackingSlip($packing_slip)->create();
        $serial_number_2 = $this->serialNumberBuilder->setProduct($product_1)
            ->setPackingSlip($packing_slip)->create();
        $serial_number_3 = $this->serialNumberBuilder->setProduct($product_2)
            ->setPackingSlip($packing_slip)->create();

        $this->assertCount(2, $packing_slip->serialNumbers->where('product_id', $product_1->id));
        $this->assertCount(1, $packing_slip->serialNumbers->where('product_id', $product_2->id));

        $data = [
            'data' => []
        ];

        $this->postJson(route('1c.dealer-order.packing-slip.add-serial-number', ['guid' => $packing_slip->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => [],
                'success' => true
            ]);

        $packing_slip->refresh();

        $this->assertCount(0, $packing_slip->serialNumbers->where('product_id', $product_1->id));
        $this->assertCount(0, $packing_slip->serialNumbers->where('product_id', $product_2->id));
    }

    /** @test */
    public function ignore_if_not_items(): void
    {
        $this->loginAsModerator();

        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        /** @var $packingSlip PackingSlip */
        $packingSlip = $this->packingSlipBuilder->setOrder($order)->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();

        $item_2 = $this->packingSlipItemBuilder->setProduct($product_2)
            ->setPackingSlip($packingSlip)->create();

        $data = [
            'data' => [
                [
                    'guid' => $product_1->guid,
                    'serial_numbers' => [
                        $this->faker->creditCardNumber,
                    ]
                ],
                [
                    'guid' => $product_3->guid,
                    'serial_numbers' => [
                        $this->faker->creditCardNumber,
                    ]
                ]
            ],
        ];

        $this->assertEmpty($packingSlip->serialNumbers);

        $this->postJson(route('1c.dealer-order.packing-slip.add-serial-number', ['guid' => $packingSlip->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    data_get($data, 'data.0.guid'),
                    data_get($data, 'data.1.guid')
                ],
                'success' => true
            ])
            ->assertJsonCount(2, 'data')
        ;

        $packingSlip->refresh();

        $this->assertEmpty($packingSlip->serialNumbers);
    }

    /** @test */
    public function ignore_if_not_product(): void
    {
        $this->loginAsModerator();

        $company = $this->companyBuilder->create();
        $dealer = $this->dealerBuilder->setCompany($company)->create();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        /** @var $packingSlip PackingSlip */
        $packingSlip = $this->packingSlipBuilder->setOrder($order)->create();

        $product_2 = $this->productBuilder->create();

        $item_2 = $this->packingSlipItemBuilder->setProduct($product_2)
            ->setPackingSlip($packingSlip)->create();

        $data = [
            'data' => [
                [
                    'guid' => $this->faker->uuid,
                    'serial_numbers' => [
                        $this->faker->creditCardNumber,
                    ]
                ],
                [
                    'guid' => $this->faker->uuid,
                    'serial_numbers' => [
                        $this->faker->creditCardNumber,
                    ]
                ]
            ],
        ];

        $this->assertEmpty($order->serialNumbers);

        $this->postJson(route('1c.dealer-order.packing-slip.add-serial-number', ['guid' => $packingSlip->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    data_get($data, 'data.0.guid'),
                    data_get($data, 'data.1.guid')
                ],
                'success' => true
            ])
            ->assertJsonCount(2, 'data')
        ;

        $packingSlip->refresh();

        $this->assertEmpty($packingSlip->serialNumbers);
    }

    /** @test */
    public function fail_not_packing_slip(): void
    {
        $this->loginAsModerator();

        $product_1 = $this->productBuilder->create();
        $data = [
            'data' => [
                [
                    'guid' => $product_1->guid,
                    'serial_numbers' => [
                        $this->faker->creditCardNumber,
                    ]
                ]
            ],
        ];

        $guid = '342342423';
        $this->postJson(route('1c.dealer-order.packing-slip.add-serial-number', ['guid' => $guid]), $data)
            ->assertStatus(404)
            ->assertJson([
                'data' => __('exceptions.dealer.order.packing_slip.not found by guid' , ['guid' => $guid]),
                'success' => false
            ]);
    }

    /** @test */
    public function fail_something_wrong_to_service(): void
    {
        $this->loginAsModerator();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $packingSlip PackingSlip */
        $packingSlip = $this->packingSlipBuilder->setOrder($order)->create();

        $product_1 = $this->productBuilder->create();

        $data = [
            'data' => [
                [
                    'guid' => $product_1->guid,
                    'serial_numbers' => [
                        $this->faker->creditCardNumber,
                    ]
                ]
            ],
        ];

        $this->mock(PackingSlipService::class, function(MockInterface $mock){
            $mock->shouldReceive("addSerialNumbers")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('1c.dealer-order.packing-slip.add-serial-number', ['guid' => $packingSlip->guid]), $data)
            ->assertStatus(500)
            ->assertJson([
                'data' => "some exception message",
                'success' => false
            ]);
    }
}
