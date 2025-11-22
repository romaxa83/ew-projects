<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\Payment;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderShowTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_not_show_for_unauthorized_users(): void
    {
        $order = factory(Order::class)->create();

        $this->getJson(route('body-shop.orders.show', $order))->assertUnauthorized();
    }

    public function test_it_not_show_for_not_permitted_users(): void
    {
        $order = factory(Order::class)->create();

        $this->loginAsBodyShopMechanic();

        $this->getJson(route('body-shop.orders.show', $order))
            ->assertForbidden();
    }

    public function test_it_show_for_permitted_users(): void
    {
        $owner = $this->ownerFactory();
        $truck = factory(Truck::class)->create(['owner_id' => $owner->id]);
        $order = factory(Order::class)->create(['truck_id' => $truck->id]);
        $typeOfWork = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 2,
            'price' => $inventory->price_retail,
        ]);
        factory(Payment::class)->create(['order_id' => $order->id]);

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.orders.show', $order))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'order_number',
                'vehicle' => [
                    'id',
                    'vin',
                    'unit_number',
                    'license_plate',
                    'temporary_plate',
                    'make',
                    'model',
                    'year',
                    'type',
                    'tags',
                    'vehicle_form',
                ],
                'discount',
                'tax_labor',
                'tax_inventory',
                'implementation_date',
                'due_date',
                'notes',
                'mechanic' => [
                    'id',
                    'first_name',
                    'last_name',
                    'phone',
                    'email',
                ],
                'customer' => [
                    'id',
                    'first_name',
                    'last_name',
                    'phone',
                    'email',
                    'phone_extension',
                ],
                'types_of_work' => [
                    '*' => [
                        'id',
                        'name',
                        'duration',
                        'hourly_rate',
                        'inventories' => [
                            '*' => [
                                'id',
                                'name',
                                'stock_number',
                                'price',
                                'quantity',
                                'total_amount',
                            ],
                        ],
                        'total_amount',
                    ],
                ],
                'payments' => [
                    '*' => [
                        'id',
                        'amount',
                        'payment_method',
                        'payment_method_name',
                        'payment_date',
                        'notes',
                    ],
                ],
                'status',
                'payment_status',
                'total_amount',
                'attachments',
                'is_prices_changed',
                'status_changed_at',
            ]]);
    }

    public function test_is_price_changed_field(): void
    {
        $owner = $this->ownerFactory();
        $truck = factory(Truck::class)->create(['owner_id' => $owner->id]);
        $order = factory(Order::class)->create(['truck_id' => $truck->id]);
        $typeOfWork = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 2,
            'price' => $inventory->price_retail,
        ]);

        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
            'price' => $inventory->price_retail,
        ]);

        $typeOfWork = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 2,
            'price' => $inventory->price_retail,
        ]);

        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
            'price' => $inventory->price_retail,
        ]);

        $this->loginAsBodyShopSuperAdmin();

        $response = $this->getJson(route('body-shop.orders.show', $order))
            ->assertOk();

        $this->assertFalse($response['data']['is_prices_changed']);

        $order->refresh();
        $inventory = $order->typesOfWork[1]->inventories[1]->inventory;
        $inventory->price_retail = $inventory->price_retail + 100;
        $inventory->save();

        $response = $this->getJson(route('body-shop.orders.show', $order))
            ->assertOk();

        $this->assertTrue($response['data']['is_prices_changed']);
    }

    public function test_it_show_with_deleted_vehicle(): void
    {
        $owner = $this->ownerFactory();
        $truck = factory(Truck::class)->create(['owner_id' => $owner->id]);
        $order = factory(Order::class)->create(['truck_id' => $truck->id]);
        factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $truck->delete();

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.orders.show', $order))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'order_number',
                'vehicle' => [
                    'id',
                    'vin',
                    'unit_number',
                    'license_plate',
                    'temporary_plate',
                    'make',
                    'model',
                    'year',
                    'type',
                    'tags',
                    'vehicle_form',
                ],
                'discount',
                'tax_labor',
                'tax_inventory',
                'implementation_date',
                'due_date',
                'notes',
                'mechanic' => [
                    'id',
                    'first_name',
                    'last_name',
                    'phone',
                    'email',
                ],
                'customer' => [
                    'id',
                    'first_name',
                    'last_name',
                    'phone',
                    'email',
                    'phone_extension',
                ],
                'types_of_work' => [
                    '*' => [
                        'id',
                        'name',
                        'duration',
                        'hourly_rate',
                        'inventories' => [],
                        'total_amount',
                    ],
                ],
                'payments' => [],
                'status',
                'payment_status',
                'total_amount',
                'attachments',
                'is_prices_changed',
                'status_changed_at',
            ]]);
    }

    public function test_it_show_with_deleted_inventory(): void
    {
        $owner = $this->ownerFactory();
        $truck = factory(Truck::class)->create(['owner_id' => $owner->id]);
        $order = factory(Order::class)->create(['truck_id' => $truck->id]);
        $typeOfWork = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create(['quantity' => 0]);
        factory(TypeOfWorkInventory::class)->create([
            'inventory_id' => $inventory->id,
            'type_of_work_id' => $typeOfWork->id,
            'quantity' => 3,
        ]);
        $inventory->delete();

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.orders.show', $order))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'order_number',
                'vehicle' => [
                    'id',
                    'vin',
                    'unit_number',
                    'license_plate',
                    'temporary_plate',
                    'make',
                    'model',
                    'year',
                    'type',
                    'tags',
                    'vehicle_form',
                ],
                'discount',
                'tax_labor',
                'tax_inventory',
                'implementation_date',
                'due_date',
                'notes',
                'mechanic' => [
                    'id',
                    'first_name',
                    'last_name',
                    'phone',
                    'email',
                ],
                'customer' => [
                    'id',
                    'first_name',
                    'last_name',
                    'phone',
                    'email',
                    'phone_extension',
                ],
                'types_of_work' => [
                    '*' => [
                        'id',
                        'name',
                        'duration',
                        'hourly_rate',
                        'inventories' => [
                            '*' => [
                                'id',
                                'inventory_id',
                                'name',
                                'stock_number',
                                'price',
                                'quantity',
                            ],
                        ],
                        'total_amount',
                    ],
                ],
                'payments' => [],
                'status',
                'payment_status',
                'total_amount',
                'attachments',
                'is_prices_changed',
                'status_changed_at',
            ]]);
    }

    public function test_it_show_with_deleted_mechanic(): void
    {
        $owner = $this->ownerFactory();
        $mechanic = $this->bsMechanicFactory();
        $truck = factory(Truck::class)->create(['owner_id' => $owner->id]);
        $order = factory(Order::class)->create(['truck_id' => $truck->id, 'mechanic_id' => $mechanic->id]);
        factory(TypeOfWork::class)->create(['order_id' => $order->id]);


        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.orders.show', $order))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'order_number',
                'vehicle' => [
                    'id',
                    'vin',
                    'unit_number',
                    'license_plate',
                    'temporary_plate',
                    'make',
                    'model',
                    'year',
                    'type',
                    'tags',
                    'vehicle_form',
                ],
                'discount',
                'tax_labor',
                'tax_inventory',
                'implementation_date',
                'due_date',
                'notes',
                'mechanic' => [
                    'id',
                    'first_name',
                    'last_name',
                    'phone',
                    'email',
                ],
                'customer' => [
                    'id',
                    'first_name',
                    'last_name',
                    'phone',
                    'email',
                    'phone_extension',
                ],
                'types_of_work' => [
                    '*' => [
                        'id',
                        'name',
                        'duration',
                        'hourly_rate',
                        'inventories' => [],
                        'total_amount',
                    ],
                ],
                'payments' => [],
                'status',
                'payment_status',
                'total_amount',
                'attachments',
                'is_prices_changed',
                'status_changed_at',
            ]]);
    }
}
