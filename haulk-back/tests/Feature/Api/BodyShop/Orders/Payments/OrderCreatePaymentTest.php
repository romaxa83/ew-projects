<?php

namespace Api\BodyShop\Orders\Payments;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\Payment;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderCreatePaymentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_payment(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $vehicleOwner = factory(VehicleOwner::class)->create();
        $truck = factory(Truck::class)->create(['customer_id' => $vehicleOwner->id]);
        $order = factory(Order::class)->create(['truck_id' => $truck->id]);
        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);

        $order->refresh();
        $order->setAmounts();

        $this->postJson(
            route('body-shop.orders.add-payment', $order),
            [
                'amount' => $order->getAmount(),
                'payment_date' => now()->format('m/d/Y'),
                'payment_method' => Payment::PAYMENT_METHOD_VENMO,
                'notes' => 'test',
            ]
        )
            ->assertCreated();

        $this->assertDatabaseHas(
            Payment::TABLE_NAME,
            [
                'order_id' => $order->id,
                'payment_method' => Payment::PAYMENT_METHOD_VENMO,
                'amount' => $order->getAmount(),
                'notes' => 'test',
            ],
        );

        $order->refresh();
        $this->assertTrue($order->is_paid);
        $this->assertNotNull($order->paid_at);
    }

    public function test_create_payment_with_part_amount(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $vehicleOwner = factory(VehicleOwner::class)->create();
        $truck = factory(Truck::class)->create(['customer_id' => $vehicleOwner->id]);
        $order = factory(Order::class)->create(['truck_id' => $truck->id]);
        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);

        $order->refresh();
        $order->setAmounts();

        $this->postJson(
            route('body-shop.orders.add-payment', $order),
            [
                'amount' => $order->getAmount() - 1,
                'payment_date' => now()->format('m/d/Y'),
                'payment_method' => Payment::PAYMENT_METHOD_VENMO,
                'notes' => 'test',
            ]
        )
            ->assertCreated();

        $this->assertDatabaseHas(
            Payment::TABLE_NAME,
            [
                'order_id' => $order->id,
                'payment_method' => Payment::PAYMENT_METHOD_VENMO,
                'amount' => $order->getAmount() - 1,
                'notes' => 'test',
            ],
        );

        $order->refresh();
        $this->assertFalse($order->is_paid);
        $this->assertNull($order->paid_at);

        $this->postJson(
            route('body-shop.orders.add-payment', $order),
            [
                'amount' => 1,
                'payment_date' => now()->format('m/d/Y'),
                'payment_method' => Payment::PAYMENT_METHOD_VENMO,
                'notes' => 'test',
            ]
        )
            ->assertCreated();

        $order->refresh();
        $this->assertTrue($order->is_paid);
        $this->assertNotNull($order->paid_at);
    }

    public function test_create_payment_with_reference_number(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $vehicleOwner = factory(VehicleOwner::class)->create();
        $truck = factory(Truck::class)->create(['customer_id' => $vehicleOwner->id]);
        $order = factory(Order::class)->create(['truck_id' => $truck->id]);
        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);

        $order->refresh();
        $order->setAmounts();

        $this->postJson(
            route('body-shop.orders.add-payment', $order),
            [
                'amount' => $order->getAmount(),
                'payment_date' => now()->format('m/d/Y'),
                'payment_method' => Payment::PAYMENT_METHOD_MONEY_ORDER,
                'notes' => 'test',
                'reference_number' => 'JHGSDSd'
            ]
        )
            ->assertCreated();

        $this->assertDatabaseHas(
            Payment::TABLE_NAME,
            [
                'order_id' => $order->id,
                'payment_method' => Payment::PAYMENT_METHOD_MONEY_ORDER,
                'amount' => $order->getAmount(),
                'notes' => 'test',
                'reference_number' => 'JHGSDSd'
            ],
        );

        $order->refresh();
        $this->assertTrue($order->is_paid);
        $this->assertNotNull($order->paid_at);
    }
}
