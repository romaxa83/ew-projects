<?php

namespace Commands\BodyShop;

use App\Console\Commands\BodyShop\ProcessFinishedPaidOrders;
use App\Models\BodyShop\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProcessFinishedPaidOrdersTest extends TestCase
{
    use DatabaseTransactions;

    public function test_deleted_orders_delete(): void
    {
        $order1 = factory(Order::class)->create([
            'status' => Order::STATUS_FINISHED,
            'is_paid' => true,
            'status_changed_at' => now()->addDays(-740)
        ]);
        $order2 = factory(Order::class)->create([
            'status' => Order::STATUS_FINISHED,
            'is_paid' => true,
            'status_changed_at' => now()->addDays(-300)
        ]);
        $order3 = factory(Order::class)->create([
            'status' => Order::STATUS_FINISHED,
            'is_paid' => false,
            'status_changed_at' => now()->addDays(-740)
        ]);
        $order4 = factory(Order::class)->create([
            'status' => Order::STATUS_IN_PROCESS,
            'is_paid' => true,
            'status_changed_at' => now()->addDays(-740)
        ]);

        // call delete
        $this->artisan(ProcessFinishedPaidOrders::class)
            ->assertExitCode(0);

        $this->assertDatabaseHas(Order::TABLE_NAME, ['id' => $order2->id]);
        $this->assertDatabaseHas(Order::TABLE_NAME, ['id' => $order3->id]);
        $this->assertDatabaseHas(Order::TABLE_NAME, ['id' => $order4->id]);
        $this->assertDatabaseMissing(Order::TABLE_NAME, ['id' => $order1->id]);
    }
}
