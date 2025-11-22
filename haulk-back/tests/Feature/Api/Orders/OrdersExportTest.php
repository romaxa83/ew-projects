<?php

namespace Api\Orders;

use App\Models\Orders\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class OrdersExportTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    public function test_get_orders_export()
    {
        $orders = $this->generateFakeOrdersWithAllStatuses();
        foreach ($orders as $order) {
            Vehicle::factory()->create(['order_id' => $order->id]);
        }
        $this->generateFakeOrderWithAllData();

        $this->loginAsCarrierSuperAdmin();

        $result = $this->getJson(
            route('orders.export', [
                'date_from' => now()->addDays(-3)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ])
        )
            ->assertOk();

        $filename = sprintf(
            'orders-%s_%s.xlsx',
            now()->addDays(-3)->format('Y-m-d'),
            now()->format('Y-m-d')
        );

        $this->assertEquals(
            'attachment; filename=' . $filename,
            $result->headers->get('content-disposition')
        );
    }

    public function test_get_orders_export_by_admin_and_accountant()
    {
        $this->generateFakeOrderWithAllData();

        $this->loginAsCarrierAdmin();

        $this->getJson(
            route('orders.export', [
                'date_from' => now()->addDays(-3)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ])
        )
            ->assertOk();

        $this->loginAsCarrierAccountant();

        $this->getJson(
            route('orders.export', [
                'date_from' => now()->addDays(-3)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ])
        )
            ->assertOk();
    }

    public function test_get_orders_export_for_not_auth_user()
    {
        $this->generateFakeOrdersWithAllStatuses();

        $this->getJson(
            route('orders.export', [
                'date_from' => now()->addDays(-3)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ])
        )
            ->assertUnauthorized();
    }

    public function test_get_orders_export_for_not_permited_user()
    {
        $this->generateFakeOrdersWithAllStatuses();

        $this->loginAsCarrierDispatcher();

        $this->getJson(
            route('orders.export', [
                'date_from' => now()->addDays(-3)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ])
        )
            ->assertForbidden();
    }
}
