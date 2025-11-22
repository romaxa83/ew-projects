<?php

namespace Unit\Events\Alerts;

use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertOrderEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Models\Admins\Admin;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderCreatePermission;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderSavedEventTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    /**
     * @throws Exception
     */
    public function test_saved_order_by_technician(): void
    {
        $admin = Admin::factory()
            ->create();

        $order = $this->createCreatedOrder();

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::ORDER . '_' . AlertOrderEnum::CREATE,
                'model_id' => $order->id,
                'model_type' => $order::MORPH_NAME
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $admin->id,
                'recipient_type' => $admin::MORPH_NAME
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_saved_order_by_admin(): void
    {
        $this->loginByAdminManager([OrderCreatePermission::KEY]);

        $order = $this->createCreatedOrder();

        $this->assertDatabaseMissing(
            Alert::class,
            [
                'type' => AlertModelEnum::ORDER . '_' . AlertOrderEnum::CREATE,
                'model_id' => $order->id,
                'model_type' => $order::MORPH_NAME
            ]
        );
    }

    public function test_changed_order_status(): void
    {
        $order = $this->createCreatedOrder();
        $order = Order::find($order->id);

        $order->status = OrderStatusEnum::PENDING_PAID;
        $order->save();

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::ORDER . '_' . AlertOrderEnum::CHANGE_STATUS,
                'model_id' => $order->id,
                'model_type' => $order::MORPH_NAME
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $order->technician->id,
                'recipient_type' => $order->technician::MORPH_NAME
            ]
        );
    }
}
