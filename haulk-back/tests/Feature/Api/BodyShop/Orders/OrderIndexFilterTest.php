<?php

namespace Tests\Feature\Api\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search(): void
    {
        $this->loginAsBodyShopAdmin();

        $truck1 = factory(Truck::class)->create(['vin' => 'TEST234234234']);
        $truck2 = factory(Truck::class)->create(['vin' => 'JHGHJ23423JHGJY']);
        $trailer1 = factory(Trailer::class)->create(['vin' => 'TEST7653456765']);
        $vehicleOwner = factory(VehicleOwner::class)->create(['first_name' => 'TEST']);
        $trailer2 = factory(Trailer::class)->create(['customer_id' => $vehicleOwner->id]);

        factory(Order::class)->create(['truck_id' => $truck1->id]);
        factory(Order::class)->create(['truck_id' => $truck2->id]);
        factory(Order::class)->create(['trailer_id' => $trailer1->id]);
        factory(Order::class)->create(['order_number' => 'test123']);
        factory(Order::class)->create(['trailer_id' => $trailer2->id]);

        $response = $this->getJson(route('body-shop.orders.index', ['q' => 'test']))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(4, $orders);
    }

    public function test_search_by_unit_number(): void
    {
        $this->loginAsBodyShopAdmin();

        $truck1 = factory(Truck::class)->create(['unit_number' => 'TEST23423']);
        $truck2 = factory(Truck::class)->create(['unit_number' => 'JHGHJ2342']);
        $trailer1 = factory(Trailer::class)->create(['unit_number' => 'TEST23453']);

        factory(Order::class)->create(['truck_id' => $truck1->id]);
        factory(Order::class)->create(['truck_id' => $truck2->id]);
        factory(Order::class)->create(['trailer_id' => $trailer1->id]);

        $response = $this->getJson(route('body-shop.orders.index', ['q' => 'TEST234']))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(2, $orders);
    }

    public function test_filter_by_year(): void
    {
        $this->loginAsBodyShopAdmin();

        $truck1 = factory(Truck::class)->create(['year' => '2020']);
        $truck2 = factory(Truck::class)->create();
        $trailer1 = factory(Trailer::class)->create(['year' => 2020]);

        factory(Order::class)->create(['truck_id' => $truck1->id]);
        factory(Order::class)->create(['truck_id' => $truck2->id]);
        factory(Order::class)->create(['trailer_id' => $trailer1->id]);

        $response = $this->getJson(route('body-shop.orders.index', ['vehicle_year' => '2020']))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(2, $orders);
    }

    public function test_filter_by_make(): void
    {
        $this->loginAsBodyShopAdmin();

        $truck1 = factory(Truck::class)->create(['make' => 'TEST']);
        $truck2 = factory(Truck::class)->create();
        $trailer1 = factory(Trailer::class)->create(['make' => 'TEST']);

        factory(Order::class)->create(['truck_id' => $truck1->id]);
        factory(Order::class)->create(['truck_id' => $truck2->id]);
        factory(Order::class)->create(['trailer_id' => $trailer1->id]);

        $response = $this->getJson(route('body-shop.orders.index', ['vehicle_make' => 'TEST']))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(2, $orders);
    }

    public function test_filter_by_model(): void
    {
        $this->loginAsBodyShopAdmin();

        $truck1 = factory(Truck::class)->create(['model' => 'TEST']);
        $truck2 = factory(Truck::class)->create();
        $trailer1 = factory(Trailer::class)->create(['model' => 'TEST']);

        factory(Order::class)->create(['truck_id' => $truck1->id]);
        factory(Order::class)->create(['truck_id' => $truck2->id]);
        factory(Order::class)->create(['trailer_id' => $trailer1->id]);

        $response = $this->getJson(route('body-shop.orders.index', ['vehicle_model' => 'TEST']))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(2, $orders);
    }

    public function test_filter_by_mechanic(): void
    {
        $this->loginAsBodyShopAdmin();
        $mechanic1 = $this->bsMechanicFactory();
        $mechanic2 = $this->bsMechanicFactory();

        factory(Order::class)->create(['mechanic_id' => $mechanic1->id]);
        factory(Order::class)->create(['mechanic_id' => $mechanic2->id]);
        factory(Order::class)->create(['mechanic_id' => $mechanic2->id]);

        $response = $this->getJson(route('body-shop.orders.index', ['mechanic_id' => $mechanic1->id]))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(1, $orders);
    }

    public function test_filter_by_status(): void
    {
        $this->loginAsBodyShopAdmin();

        factory(Order::class)->create(['status' => Order::STATUS_NEW]);
        factory(Order::class)->create(['status' => Order::STATUS_NEW]);
        factory(Order::class)->create(['status' => Order::STATUS_FINISHED]);

        $response = $this->getJson(route('body-shop.orders.index', ['status' => Order::STATUS_NEW]))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(2, $orders);
    }

    public function test_filter_by_implementation_date(): void
    {
        $this->loginAsBodyShopAdmin();

        factory(Order::class)->create(['implementation_date' => now()->addDay()]);
        factory(Order::class)->create(['implementation_date' => now()]);
        factory(Order::class)->create(['implementation_date' => now()->addDay()]);

        $response = $this->getJson(route('body-shop.orders.index', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(1, $orders);
    }

    public function test_default_ordering(): void
    {
        $this->loginAsBodyShopAdmin();

        $sameDate = now();

        $order1 = factory(Order::class)->create(['implementation_date' => now()->addDay(), 'status' => Order::STATUS_NEW]);
        $order2 = factory(Order::class)->create(['implementation_date' => $sameDate, 'status' => Order::STATUS_FINISHED]);
        $order3 = factory(Order::class)->create(['implementation_date' => $sameDate, 'status' => Order::STATUS_NEW]);
        $order4 = factory(Order::class)->create(['implementation_date' => $sameDate, 'status' => Order::STATUS_IN_PROCESS]);
        $order5 = factory(Order::class)->create(['implementation_date' => now()->addDays(2), 'status' => Order::STATUS_IN_PROCESS]);

        $response = $this->getJson(route('body-shop.orders.index'))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(5, $orders);
        $this->assertEquals($order3->id, $orders[0]['id']);
        $this->assertEquals($order1->id, $orders[1]['id']);
        $this->assertEquals($order4->id, $orders[2]['id']);
        $this->assertEquals($order5->id, $orders[3]['id']);
        $this->assertEquals($order2->id, $orders[4]['id']);
    }

    public function test_search_by_owner_customer(): void
    {
        $this->loginAsBodyShopAdmin();

        $customer = factory(VehicleOwner::class)->create(['first_name' => 'TESTCustomer']);
        $owner1 = $this->ownerFactory(['first_name' => 'testowner']);
        $owner2 = $this->ownerFactory(['first_name' => 'ownername']);

        $truck1 = factory(Truck::class)->create();
        $truck2 = factory(Truck::class)->create(['owner_id' => $owner2->id, 'carrier_id' => 1]);
        $trailer1 = factory(Trailer::class)->create(['owner_id' => $owner1->id, 'carrier_id' => 1]);
        $trailer2 = factory(Trailer::class)->create(['customer_id' => $customer->id]);

        factory(Order::class)->create(['truck_id' => $truck1->id]);
        factory(Order::class)->create(['truck_id' => $truck2->id]);
        factory(Order::class)->create(['trailer_id' => $trailer1->id]);
        factory(Order::class)->create();
        factory(Order::class)->create(['trailer_id' => $trailer2->id]);

        $response = $this->getJson(route('body-shop.orders.index', ['q' => 'test']))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(2, $orders);
    }

    public function test_filter_by_deleted_status(): void
    {
        $this->loginAsBodyShopAdmin();


        factory(Order::class)->create(['deleted_at' => now(), 'status' => Order::STATUS_DELETED]);
        factory(Order::class)->create();
        factory(Order::class)->create();

        $response = $this->getJson(route('body-shop.orders.index', ['status' => Order::STATUS_DELETED]))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(1, $orders);
    }

    public function test_filter_by_payment_status(): void
    {
        $this->loginAsBodyShopAdmin();

        $new = factory(Order::class)->create([
            'is_billed' => false,
            'billed_at' => null,
            'is_paid' => false,
            'paid_at' => null,
            'due_date' => now()->addDay(),
        ]);
        $billed = factory(Order::class)->create([
            'is_billed' => true,
            'billed_at' => now(),
            'is_paid' => false,
            'paid_at' => null,
            'due_date' => now()->addDay(),
        ]);
        $paid = factory(Order::class)->create([
            'is_billed' => true,
            'billed_at' => now(),
            'is_paid' => true,
            'paid_at' => now(),
            'due_date' => now()->addDay(),
        ]);
        $paidNotBilled = factory(Order::class)->create([
            'is_billed' => false,
            'billed_at' => null,
            'is_paid' => true,
            'paid_at' => now(),
            'due_date' => now()->addDays(-1),
        ]);
        $overdue = factory(Order::class)->create([
            'is_billed' => true,
            'billed_at' => now(),
            'is_paid' => false,
            'paid_at' => null,
            'due_date' => now()->addDays(-1),
        ]);
        $notOverdue = factory(Order::class)->create([
            'is_billed' => true,
            'billed_at' => now(),
            'is_paid' => false,
            'paid_at' => null,
            'due_date' => now()->addDays(1),
        ]);

        $response = $this->getJson(route('body-shop.orders.index', ['payment_status' => Order::PAYMENT_STATUS_PAID]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(2, $orders);
        $expected = [$paidNotBilled->id, $paid->id];
        $actual = array_column($orders, 'id');
        $this->assertEquals(sort($expected), sort($actual));

        $response = $this->getJson(route('body-shop.orders.index', ['payment_status' => Order::PAYMENT_STATUS_NOT_PAID]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(4, $orders);
        $expected = [$new->id, $billed->id, $overdue->id, $notOverdue->id];
        $actual = array_column($orders, 'id');
        $this->assertEquals(sort($expected), sort($actual));

        $response = $this->getJson(route('body-shop.orders.index', ['payment_status' => Order::PAYMENT_STATUS_BILLED]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(3, $orders);
        $expected = [$billed->id, $overdue->id, $notOverdue->id];
        $actual = array_column($orders, 'id');
        $this->assertEquals(sort($expected), sort($actual));


        $response = $this->getJson(route('body-shop.orders.index', ['payment_status' => Order::PAYMENT_STATUS_NOT_BILLED]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(1, $orders);
        $this->assertEquals($new->id, $orders[0]['id']);

        $response = $this->getJson(route('body-shop.orders.index', ['payment_status' => Order::PAYMENT_STATUS_OVERDUE]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(1, $orders);
        $this->assertEquals($overdue->id, $orders[0]['id']);

        $response = $this->getJson(route('body-shop.orders.index', ['payment_status' => Order::PAYMENT_STATUS_NOT_OVERDUE]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(4, $orders);
        $expected = [$new->id, $paid->id, $notOverdue->id, $billed->id];
        $actual = array_column($orders, 'id');
        $this->assertEquals(sort($expected), sort($actual));
    }

    public function test_filter_by_inventory(): void
    {
        $this->loginAsBodyShopAdmin();

        $truck = factory(Truck::class)->create(['vin' => 'TEST234234234']);

        $order1 = factory(Order::class)->create(['truck_id' => $truck->id]);
        $inventory1 = factory(Inventory::class)->create();
        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order1->id]);
        factory(TypeOfWorkInventory::class)->create([
            'inventory_id' => $inventory1->id,
            'quantity' => 10,
            'type_of_work_id' => $typeOfWork1->id,
        ]);

        $order2 = factory(Order::class)->create(['truck_id' => $truck->id]);
        $inventory2 = factory(Inventory::class)->create();
        $typeOfWork2 = factory(TypeOfWork::class)->create(['order_id' => $order2->id]);
        factory(TypeOfWorkInventory::class)->create([
            'inventory_id' => $inventory2->id,
            'quantity' => 10,
            'type_of_work_id' => $typeOfWork2->id,
        ]);

        $response = $this->getJson(route('body-shop.orders.index', ['inventory_id' => $inventory1->id]))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(1, $orders);
        $this->assertEquals($order1->id, $orders[0]['id']);
    }

    public function test_filter_by_truck_and_trailer(): void
    {
        $this->loginAsBodyShopAdmin();

        $truck1 = factory(Truck::class)->create();
        $truck2 = factory(Truck::class)->create();
        $trailer1 = factory(Trailer::class)->create();
        $trailer2 = factory(Trailer::class)->create();

        factory(Order::class)->create(['truck_id' => $truck1->id]);
        factory(Order::class)->create(['truck_id' => $truck2->id]);
        factory(Order::class)->create(['trailer_id' => $trailer1->id]);
        factory(Order::class)->create(['trailer_id' => $trailer2->id]);

        $response = $this->getJson(route('body-shop.orders.index', ['truck_id' => $truck1->id]))
            ->assertOk();

        $orders = $response->json('data');
        $this->assertCount(1, $orders);
        $this->assertEquals($truck1->id, $orders[0]['vehicle']['id']);

        $response = $this->getJson(route('body-shop.orders.index', ['trailer_id' => $trailer2->id]))
            ->assertOk();

        $orders = $response->json('data');
        $this->assertCount(1, $orders);
        $this->assertEquals($trailer2->id, $orders[0]['vehicle']['id']);
    }
}
