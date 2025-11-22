<?php

namespace Commands\BodyShop;

use App\Console\Commands\BodyShop\PurgeDeletedOrders;
use App\Models\BodyShop\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurgeDeletedOrdersTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_deleted_orders_delete(): void
    {
        $order1 = factory(Order::class)->create();
        $order2 = factory(Order::class)->create(['deleted_at' => now()->addDays(-32)]);
        $order3 = factory(Order::class)->create(['deleted_at' => now()->addDays(-15)]);

        // call delete
        $this->artisan(PurgeDeletedOrders::class)
            ->assertExitCode(0);

        $this->assertDatabaseHas(Order::TABLE_NAME, ['id' => $order1->id]);
        $this->assertDatabaseHas(Order::TABLE_NAME, ['id' => $order3->id]);
        $this->assertDatabaseMissing(Order::TABLE_NAME, ['id' => $order2->id]);
    }
}
