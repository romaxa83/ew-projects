<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Category;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Transaction;
use App\Models\BodyShop\Inventories\Unit;
use App\Models\BodyShop\Suppliers\Supplier;
use App\Models\BodyShop\Orders\Order;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class InventoryHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $inventory = factory(Inventory::class)->create();

        $this->getJson(route('body-shop.inventories.histories', $inventory->id))
            ->assertUnauthorized();

        $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $inventory = factory(Inventory::class)->create();
        $this->getJson(route('body-shop.inventories.histories', $inventory->id))
            ->assertForbidden();

        $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertForbidden();
    }

    public function create_and_update_inventory(): int
    {
        $formRequest = [
            'name' => 'Name Test',
            'stock_number' => 'JHGJHg3434',
            'price_retail' => 30.20,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create())->id,
            'purchase' => [
                'quantity' => 10,
                'cost' => 20.25,
                'invoice_number' => 'SDSASD23324',
                'date' => now()->format('m/d/Y'),
            ],
        ];

        $data = $this->postJson(route('body-shop.inventories.store'), $formRequest)
            ->assertCreated();

        $id = $data['data']['id'];
        $inventory = Inventory::find($id);

        $formRequest = [
            'name' => 'Name Test2',
            'stock_number' => 'JHGJHg34342',
            'price_retail' => 31.20,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => $inventory->supplier->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create())->id,
        ];

        $this->putJson(route('body-shop.inventories.update', $inventory), $formRequest)
            ->assertOk();

        return $id;
    }

    public function test_it_get_history(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $id = $this->create_and_update_inventory();

        $response = $this->getJson(route('body-shop.inventories.histories', $id))
            ->assertOk();
        $this->assertCount(3, $response['data']);

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $id))
            ->assertOk();
        $this->assertCount(3, $response['data']);
    }

    public function test_history_on_using_inventory_in_order()
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $inventory = factory(Inventory::class)->create(['quantity' => 100]);

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 3],
                    ],
                ]
            ],
        ];

        $response = $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        /** @var Order $order */
        $order = Order::find($response['data']['id']);

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(1, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);

        $formRequest['types_of_work'][0]['id'] = $order->typesOfWork[0]->id;
        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(1, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);

        $formRequest['types_of_work'][0]['inventories'][0]['quantity'] = 4;
        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(2, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);

        $formRequest['types_of_work'][0]['inventories'][0]['quantity'] = 2;
        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(3, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);

        $inventory->price_retail = 100;
        $inventory->save();
        $formRequest['need_to_update_prices'] = true;
        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(3, $response['data']);

        $response = $this->getJson(route('body-shop.inventories.histories', $inventory->id))
            ->assertOk();

        $this->assertCount(4, $response['data']);
    }

    public function test_history_on_changing_order_status()
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $inventory = factory(Inventory::class)->create(['quantity' => 100]);

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 3],
                    ],
                ]
            ],
        ];

        $response = $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        /** @var Order $order */
        $order = Order::find($response['data']['id']);

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(1, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);

        $attributes = ['status' => Order::STATUS_IN_PROCESS];
        $this->putJson(route('body-shop.orders.change-status', $order), $attributes)
            ->assertOk();

        $response = $this->getJson(route('body-shop.inventories.histories', $inventory->id))
            ->assertOk();

        $this->assertCount(1, $response['data']);

        $attributes = ['status' => Order::STATUS_FINISHED];
        $this->putJson(route('body-shop.orders.change-status', $order), $attributes)
            ->assertOk();

        $response = $this->getJson(route('body-shop.inventories.histories', $inventory->id))
            ->assertOk();

        $this->assertCount(2, $response['data']);

        $attributes = ['status' => Order::STATUS_IN_PROCESS];
        $this->putJson(route('body-shop.orders.change-status', $order), $attributes)
            ->assertOk();

        $response = $this->getJson(route('body-shop.inventories.histories', $inventory->id))
            ->assertOk();

        $this->assertCount(3, $response['data']);
    }

    public function test_history_on_order_deleting()
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $inventory = factory(Inventory::class)->create(['quantity' => 100]);

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 3],
                    ],
                ]
            ],
        ];

        $response = $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        /** @var Order $order */
        $order = Order::find($response['data']['id']);

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(1, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);

        $this->deleteJson(route('body-shop.orders.destroy', $order))
            ->assertNoContent();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(2, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);
    }

    public function test_history_on_order_restoring()
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $inventory = factory(Inventory::class)->create(['quantity' => 100]);

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 3],
                    ],
                ]
            ],
        ];

        $response = $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        /** @var Order $order */
        $order = Order::find($response['data']['id']);

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(1, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);

        $this->deleteJson(route('body-shop.orders.destroy', $order))
            ->assertNoContent();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(2, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);

        $this->putJson(route('body-shop.orders.restore', $order))
            ->assertOk();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(3, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);
    }

    public function test_history_on_order_restoring_with_editing()
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $inventory = factory(Inventory::class)->create(['quantity' => 100]);

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 3],
                    ],
                ]
            ],
        ];

        $response = $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        /** @var Order $order */
        $order = Order::find($response['data']['id']);

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(1, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);

        $this->deleteJson(route('body-shop.orders.destroy', $order))
            ->assertNoContent();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(2, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 3],
                    ],
                ]
            ],
        ];

        $this->postJson(route('body-shop.orders.restore-with-editing', $order), $formRequest)
            ->assertOk();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory->id))
            ->assertOk();

        $this->assertCount(3, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);
    }

    public function test_it_on_purchase(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $inventory = factory(Inventory::class)->create();

        $formRequest = [
            'quantity' => 10,
            'cost' => 14.3,
            'invoice_number' => 'JHG232JH',
            'date' => now()->format('m/d/Y'),
        ];

        $this->postJson(route('body-shop.inventories.purchase', $inventory), $formRequest)
            ->assertCreated();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory))
            ->assertOk();
        $this->assertCount(1, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);
    }

    public function test_it_on_sold(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $inventory = factory(Inventory::class)->create(['quantity' => 20]);

        $formRequest = [
            'quantity' => 10,
            'price' => 14.3,
            'invoice_number' => 'JHG232JH',
            'date' => now()->format('m/d/Y'),
            'describe' => Transaction::DESCRIBE_DEFECT,
        ];

        $this->postJson(route('body-shop.inventories.sold', $inventory), $formRequest)
            ->assertCreated();

        $response = $this->getJson(route('body-shop.inventories.histories-detailed', $inventory))
            ->assertOk();
        $this->assertCount(1, $response['data']);
        $this->assertCount(1, $response['data'][0]['histories']);
    }
}
