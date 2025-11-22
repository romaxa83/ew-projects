<?php

namespace Feature\Http\Api\V1\Orders\Parts\Shipping;

use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\ShippingMethod;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class GetMethodsTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected ItemBuilder $itemBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function list_for_pickup()
    {
        $this->loginUserAsSuperAdmin();

        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        /** @var $mode Order */
        $model = $this->orderBuilder->delivery_type(DeliveryType::Pickup)->create();

        $this->itemBuilder->order($model)->inventory($inventory_1)->free_shipping(true)->create();
        $this->itemBuilder->order($model)->inventory($inventory_2)->free_shipping(false)->create();

        $this->getJson(route('api.v1.orders.parts.shipping-methods', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    [
                        'methods' => [config('shipping.methods.test_data')[ShippingMethod::Pickup()]],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.methods')
            ->assertJsonCount(2, 'data.0.items')
        ;
    }

    /** @test */
    public function list_for_delivery_only_free_shipping()
    {
        Config::set('shipping.methods.enable_test_data', true);

        $this->loginUserAsSuperAdmin();

        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        /** @var $mode Order */
        $model = $this->orderBuilder->delivery_type(DeliveryType::Delivery)->create();

        $this->itemBuilder->order($model)->free_shipping(true)->inventory($inventory_1)->create();
        $this->itemBuilder->order($model)->free_shipping(true)->inventory($inventory_2)->create();

        $this->getJson(route('api.v1.orders.parts.shipping-methods', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    [
                        'methods' => [config('shipping.methods.test_data')[ShippingMethod::UPS_Standard()]],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.methods')
            ->assertJsonCount(2, 'data.0.items')
        ;
    }

    /** @test */
    public function list_for_delivery_only_paid_shipping()
    {
        Config::set('shipping.methods.enable_test_data', true);

        $this->loginUserAsSuperAdmin();

        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        /** @var $mode Order */
        $model = $this->orderBuilder->delivery_type(DeliveryType::Delivery)->create();

        $this->itemBuilder->order($model)->free_shipping(false)->inventory($inventory_1)->create();
        $this->itemBuilder->order($model)->free_shipping(false)->inventory($inventory_2)->create();

        $this->getJson(route('api.v1.orders.parts.shipping-methods', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    [
                        'methods' => [
                            config('shipping.methods.test_data')[ShippingMethod::UPS_Next_Day_Air_Saver()],
                            config('shipping.methods.test_data')[ShippingMethod::UPS_Next_Day_Air()],
                            config('shipping.methods.test_data')[ShippingMethod::FedEx_Ground()],
                            config('shipping.methods.test_data')[ShippingMethod::FedEx_Express_Saver()],
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(4, 'data.0.methods')
            ->assertJsonCount(2, 'data.0.items')
        ;
    }

    /** @test */
    public function list_for_delivery_paid_and_free_shipping()
    {
        Config::set('shipping.methods.enable_test_data', true);

        $this->loginUserAsSuperAdmin();

        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        /** @var $mode Order */
        $model = $this->orderBuilder->delivery_type(DeliveryType::Delivery)->create();

        $item_1 = $this->itemBuilder->order($model)->free_shipping(false)->inventory($inventory_1)->create();
        $item_2 = $this->itemBuilder->order($model)->free_shipping(true)->inventory($inventory_2)->create();

        $this->getJson(route('api.v1.orders.parts.shipping-methods', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    [
                        'methods' => [config('shipping.methods.test_data')[ShippingMethod::UPS_Standard()]],
                        'items' => [
                            ['id' => $item_2->id],
                        ]
                    ],
                    [
                        'methods' => [
                            config('shipping.methods.test_data')[ShippingMethod::UPS_Next_Day_Air_Saver()],
                            config('shipping.methods.test_data')[ShippingMethod::UPS_Next_Day_Air()],
                            config('shipping.methods.test_data')[ShippingMethod::FedEx_Ground()],
                            config('shipping.methods.test_data')[ShippingMethod::FedEx_Express_Saver()],
                        ],
                        'items' => [
                            ['id' => $item_1->id],
                        ]

                    ]
                ]
            ])
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(1, 'data.0.methods')
            ->assertJsonCount(4, 'data.1.methods')
            ->assertJsonCount(1, 'data.0.items')
            ->assertJsonCount(1, 'data.1.items')
        ;
    }

    /** @test */
    public function list_empty_order_not_items()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $mode Order */
        $model = $this->orderBuilder->delivery_type(DeliveryType::Delivery)->create();

        $this->getJson(route('api.v1.orders.parts.shipping-methods', ['id' => $model->id]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        /** @var $mode Order */
        $model = $this->orderBuilder->delivery_type(DeliveryType::Delivery)->create();

        $res = $this->getJson(route('api.v1.orders.parts.shipping-methods', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
