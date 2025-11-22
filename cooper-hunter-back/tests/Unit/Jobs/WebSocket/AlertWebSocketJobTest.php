<?php

namespace Unit\Jobs\WebSocket;

use App\Models\Admins\Admin;
use App\Models\Alerts\Alert;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderCreatePermission;
use Core\WebSocket\Jobs\WsBroadcastJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class AlertWebSocketJobTest extends TestCase
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

    public function test_create_new_alert()
    {
        Bus::fake();

        $this->loginByAdminManager([OrderCreatePermission::KEY]);

        $order = $this->createCreatedOrder();

        Bus::assertDispatched(
            function (WsBroadcastJob $job) use ($order): bool
            {
                if ($job->getUser()->id !== $order->technician->id) {
                    return false;
                }

                if ($job->getUser()
                        ->getMorphType() !== $order->technician->getMorphType()) {
                    return false;
                }

                $context = $job->getContext();

                if (empty($context) || !array_key_exists('alert', $context)) {
                    return false;
                }

                $alert = Alert::query()
                    ->where('model_id', $order->id)
                    ->where('model_type', $order->getMorphType())
                    ->first();

                if ($context['alert'] !== $alert->id) {
                    return false;
                }

                return true;
            }
        );
    }
}
