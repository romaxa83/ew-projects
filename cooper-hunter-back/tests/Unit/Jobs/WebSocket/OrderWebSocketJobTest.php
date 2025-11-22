<?php

namespace Tests\Unit\Jobs\WebSocket;

use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Orders\OrderSubscriptionActionEnum;
use App\Models\Admins\Admin;
use App\Models\Orders\Order;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderCreatePermission;
use Core\WebSocket\Jobs\WsBroadcastJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderWebSocketJobTest extends TestCase
{
    use DatabaseTransactions;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    private Technician $technician;
    private Admin $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->technician = Technician::factory()
            ->certified()
            ->verified()
            ->create();

        $this->admin = Admin::factory()
            ->create();
    }

    public function test_job_by_create_order(): void
    {
        Bus::fake();

        $order = $this->setOrderTechnician($this->technician)
            ->createCreatedOrder();

        Bus::assertDispatched(
            function (WsBroadcastJob $job) use ($order): bool
            {
                return $this->checkJobData($job, $this->admin, $order);
            }
        );

        Bus::assertNotDispatched(
            function (WsBroadcastJob $job) use ($order): bool
            {
                return $this->checkJobData($job, $order->technician, $order);
            }
        );
    }

    private function checkJobData(
        WsBroadcastJob $job,
        Admin|Technician $user,
        Order $order,
        string $action = OrderSubscriptionActionEnum::CREATED
    ): bool
    {
        if (!$job->getUser()) {
            return false;
        }

        if ($job->getUser()->id !== $user->id) {
            return false;
        }

        if ($job->getUser()
                ->getMorphType() !== $user::MORPH_NAME) {
            return false;
        }

        $context = $job->getContext();

        if (empty($context) || !array_key_exists('id', $context) || !array_key_exists('action', $context)) {
            return false;
        }

        if ($context['id'] !== $order->id || $context['action'] !== $action) {
            return false;
        }

        return true;
    }

    public function test_job_by_admin_create_order(): void
    {
        Bus::fake();

        $this->loginByAdminManager([OrderCreatePermission::KEY]);

        $order = $this->setOrderTechnician($this->technician)
            ->createCreatedOrder();

        Bus::assertDispatched(
            function (WsBroadcastJob $job) use ($order): bool
            {
                return $this->checkJobData($job, $order->technician, $order);
            }
        );

        Bus::assertNotDispatched(
            function (WsBroadcastJob $job) use ($order): bool
            {
                return $this->checkJobData($job, $this->admin, $order);
            }
        );
    }

    public function test_job_update_order(): void
    {
        Bus::fake();

        $order = $this->setOrderTechnician($this->technician)
            ->withoutCreatedEvent()
            ->createCreatedOrder();

        $order = Order::find($order->id);

        $order->status = OrderStatusEnum::PENDING_PAID;
        $order->save();

        Bus::assertDispatched(
            function (WsBroadcastJob $job) use ($order): bool
            {
                return $this->checkJobData($job, $order->technician, $order, OrderSubscriptionActionEnum::UPDATED);
            }
        );
        Bus::assertDispatched(
            function (WsBroadcastJob $job) use ($order): bool
            {
                return $this->checkJobData($job, $this->admin, $order, OrderSubscriptionActionEnum::UPDATED);
            }
        );
    }

    public function test_job_delete_order(): void
    {
        Bus::fake();

        $order = $this->setOrderTechnician($this->technician)
            ->withoutCreatedEvent()
            ->createCreatedOrder();

        $order->delete();

        Bus::assertDispatched(
            function (WsBroadcastJob $job) use ($order): bool
            {
                return $this->checkJobData($job, $order->technician, $order, OrderSubscriptionActionEnum::DELETED);
            }
        );
        Bus::assertDispatched(
            function (WsBroadcastJob $job) use ($order): bool
            {
                return $this->checkJobData($job, $this->admin, $order, OrderSubscriptionActionEnum::DELETED);
            }
        );
    }
}
