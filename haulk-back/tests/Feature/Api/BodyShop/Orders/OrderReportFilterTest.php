<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderReportFilterTest extends TestCase
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

        $response = $this->getJson(route('body-shop.orders.report', ['q' => 'test']))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(4, $orders);
    }

    public function test_filter_by_status(): void
    {
        $this->loginAsBodyShopAdmin();

        factory(Order::class)->create(['status' => Order::STATUS_NEW]);
        factory(Order::class)->create(['status' => Order::STATUS_NEW]);
        factory(Order::class)->create(['status' => Order::STATUS_FINISHED]);

        $response = $this->getJson(route('body-shop.orders.report', ['statuses' => [Order::STATUS_NEW]]))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(2, $orders);
    }

    public function test_filter_by_implementation_date(): void
    {
        $this->loginAsBodyShopAdmin();

        factory(Order::class)->create(['implementation_date' => now()->addDay()]);
        factory(Order::class)->create(['implementation_date' => now()]);
        factory(Order::class)->create(['implementation_date' => now()->addDays(2)]);

        $response = $this->getJson(route('body-shop.orders.report', ['implementation_date_from' => now()->addDay()->format('Y-m-d')]))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(2, $orders);
    }

    public function test_default_ordering(): void
    {
        $this->loginAsBodyShopAdmin();

        //with current due
        $order1 = factory(Order::class)->create([
            'due_date' => now()->addDay(),
            'total_amount' => 100,
            'paid_amount' => 50,
            'debt_amount' => 50,
        ]);
        //with past due
        $order2 = factory(Order::class)->create([
            'due_date' => now()->addDays(-1),
            'total_amount' => 100,
            'paid_amount' => 50,
            'debt_amount' => 50,
        ]);
        //paid
        $order3 = factory(Order::class)->create([
            'due_date' => now()->addDays(-1),
            'total_amount' => 100,
            'paid_amount' => 100,
            'debt_amount' => 0,
        ]);
        //with current due
        $order4 = factory(Order::class)->create([
            'due_date' => now()->addDay(),
            'total_amount' => 100,
            'paid_amount' => 70,
            'debt_amount' => 30,
        ]);
        //with past due
        $order5 = factory(Order::class)->create([
            'due_date' => now()->addDays(-1),
            'total_amount' => 100,
            'paid_amount' => 40,
            'debt_amount' => 60,
        ]);

        $params = ['order_by' => 'current_due', 'order_type' => 'asc'];
        $response = $this->getJson(route('body-shop.orders.report', $params))
            ->assertOk();
        $this->assertEquals([$order4->id, $order1->id, $order5->id, $order3->id, $order2->id], array_column($response['data'], 'id'));

        $params = ['order_by' => 'current_due', 'order_type' => 'desc'];
        $response = $this->getJson(route('body-shop.orders.report', $params))
            ->assertOk();
        $this->assertEquals([$order1->id, $order4->id, $order5->id, $order3->id, $order2->id], array_column($response['data'], 'id'));

        $params = ['order_by' => 'past_due', 'order_type' => 'asc'];
        $response = $this->getJson(route('body-shop.orders.report', $params))
            ->assertOk();
        $this->assertEquals([$order2->id, $order5->id, $order4->id, $order3->id, $order1->id], array_column($response['data'], 'id'));

        $params = ['order_by' => 'past_due', 'order_type' => 'desc'];
        $response = $this->getJson(route('body-shop.orders.report', $params))
            ->assertOk();
        $this->assertEquals([$order5->id, $order2->id, $order4->id, $order3->id, $order1->id], array_column($response['data'], 'id'));

        $params = ['order_by' => 'total_due', 'order_type' => 'asc'];
        $response = $this->getJson(route('body-shop.orders.report', $params))
            ->assertOk();
        $this->assertEquals([$order4->id, $order2->id, $order1->id, $order5->id, $order3->id], array_column($response['data'], 'id'));

        $params = ['order_by' => 'total_due', 'order_type' => 'desc'];
        $response = $this->getJson(route('body-shop.orders.report', $params))
            ->assertOk();
        $this->assertEquals([$order5->id, $order2->id, $order1->id, $order4->id, $order3->id], array_column($response['data'], 'id'));
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

        $response = $this->getJson(route('body-shop.orders.report', ['q' => 'test']))
            ->assertOk();

        $orders = $response->json('data');

        $this->assertCount(2, $orders);
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

        $response = $this->getJson(route('body-shop.orders.report', ['payment_statuses' => [Order::PAYMENT_STATUS_PAID]]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(2, $orders);
        $expected = [$paidNotBilled->id, $paid->id];
        $actual = array_column($orders, 'id');
        $this->assertEquals(sort($expected), sort($actual));

        $response = $this->getJson(route('body-shop.orders.report', ['payment_statuses' => [Order::PAYMENT_STATUS_NOT_PAID]]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(4, $orders);
        $expected = [$new->id, $billed->id, $overdue->id, $notOverdue->id];
        $actual = array_column($orders, 'id');
        $this->assertEquals(sort($expected), sort($actual));

        $response = $this->getJson(route('body-shop.orders.report', ['payment_statuses' => [Order::PAYMENT_STATUS_BILLED]]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(3, $orders);
        $expected = [$billed->id, $overdue->id, $notOverdue->id];
        $actual = array_column($orders, 'id');
        $this->assertEquals(sort($expected), sort($actual));


        $response = $this->getJson(route('body-shop.orders.report', ['payment_statuses' => [Order::PAYMENT_STATUS_NOT_BILLED]]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(1, $orders);
        $this->assertEquals($new->id, $orders[0]['id']);

        $response = $this->getJson(route('body-shop.orders.report', ['payment_statuses' => [Order::PAYMENT_STATUS_OVERDUE]]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(1, $orders);
        $this->assertEquals($overdue->id, $orders[0]['id']);

        $response = $this->getJson(route('body-shop.orders.report', ['payment_statuses' => [Order::PAYMENT_STATUS_NOT_OVERDUE]]))
            ->assertOk();
        $orders = $response->json('data');

        $this->assertCount(4, $orders);
        $expected = [$new->id, $paid->id, $notOverdue->id, $billed->id];
        $actual = array_column($orders, 'id');
        $this->assertEquals(sort($expected), sort($actual));
    }
}
