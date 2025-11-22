<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\ProcessPaidOrders;
use App\Documents\CompanyDocument;
use App\Documents\OrderDocument;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\PaymentStage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\TestCase;

class ProcessPaidOrdersTest extends TestCase
{
    use DatabaseTransactions;
    use OrderESSavingHelper;
    use ElasticsearchClear;

    public function test_it_clear_old_orders_with_paid_status_success(): void
    {
        Config::set('orders.delete_after', 5);
        $paidAt = Carbon::now()->subDays(10)->getTimestamp();
        $order1 = Order::factory()
            ->assignedStatus()
            ->shipperFullName($this->faker->unique()->company)
            ->has(
                Payment::factory()->broker()->paid(),
                'payment'
            )
            ->create();
        $order2 = Order::factory()
            ->pickedUpStatus()
            ->shipperFullName($this->faker->unique()->company)
            ->has(
                Payment::factory()->broker()->paid(),
                'payment'
            )
            ->create();
        $order3 = Order::factory()
            ->deliveredStatus()
            ->shipperFullName($this->faker->unique()->company)
            ->has(
                Payment::factory()->broker()->paid(),
                'payment'
            )
            ->create();
        $order4 = Order::factory()
            ->deliveredStatus()
            ->shipperFullName($this->faker->unique()->company)
            ->has(
                Payment::factory()->broker()->customer()->paid(),
                'payment'
            )
            ->create();
        $order5 = Order::factory()
            ->deliveredStatus()
            ->shipperFullName($this->faker->unique()->company)
            ->has(
                Payment::factory()->customer()->paid(),
                'payment'
            )
            ->create();
        $order6 = Order::factory()
            ->deliveredStatus()
            ->shipperFullName($this->faker->unique()->company)
            ->has(
                Payment::factory()->customer()->paid()->brokerFee(),
                'payment'
            )
            ->create();
        $order7 = Order::factory()
            ->deliveredStatus()
            ->shipperFullName($this->faker->unique()->company)
            ->has(
                Payment::factory()->customer()->paid()->brokerFee()->brokerFeePaid(),
                'payment'
            )
            ->create();

        PaymentStage::query()->update(['payment_date' => $paidAt]);
        $this->makeDocuments(true);
        $this->artisan(ProcessPaidOrders::class)
            ->assertExitCode(0);
        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order1->id
            ]
        );
        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order2->id
            ]
        );
        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order6->id
            ]
        );
        $this->assertDatabaseMissing(
            Order::class,
            [
                'id' => $order3->id
            ]
        );
        $this->assertDatabaseMissing(
            Order::class,
            [
                'id' => $order4->id
            ]
        );
        $this->assertDatabaseMissing(
            Order::class,
            [
                'id' => $order5->id
            ]
        );
        $this->assertDatabaseMissing(
            Order::class,
            [
                'id' => $order7->id
            ]
        );
        sleep(1);
        $this->assertEquals(3, OrderDocument::query()->count());
        $this->assertEquals(3, CompanyDocument::query()->count());
    }
}
