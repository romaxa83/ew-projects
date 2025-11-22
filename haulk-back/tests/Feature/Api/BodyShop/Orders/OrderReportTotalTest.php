<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderReportTotalTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

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

        $response = $this->getJson(route('body-shop.orders.report-total'))
            ->assertOk();

        $this->assertEquals(80, $response['data']['current_due']);
        $this->assertEquals(110, $response['data']['past_due']);
        $this->assertEquals(190, $response['data']['total_due']);
        $this->assertEquals(500, $response['data']['total_amount']);
    }
}
