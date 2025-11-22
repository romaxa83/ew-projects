<?php

namespace Tests\Unit\Notifications\Fcm;

use App\Models\Technicians\Technician;
use App\Notifications\Alerts\AlertFcmNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;
use Tests\Traits\Permissions\RoleHelperTrait;

class AlertFcmNotificationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;
    use WithFaker;

    public function test_alert_without_fcm_token(): void
    {
        Notification::fake();

        $technician = Technician::factory()
            ->certified()
            ->verified()
            ->create();

        $this->loginAsAdminManager();

        $this->setOrderTechnician($technician)
            ->createCreatedOrder();

        Notification::assertNotSentTo(
            $technician,
            AlertFcmNotification::class,
            function (AlertFcmNotification $notification, array $channels, Technician $notifiable)
            {
                return !empty($channels);
            }
        );
    }

    public function test_alert_with_fcm_token(): void
    {
        Notification::fake();

        $technician = Technician::factory()
            ->certified()
            ->verified()
            ->create();

        $technician->fcmTokens()
            ->create(
                [
                    'token' => $this->faker->lexify
                ]
            );

        $this->loginAsAdminManager();

        $this->setOrderTechnician($technician)
            ->createCreatedOrder();

        Notification::assertSentTo($technician, AlertFcmNotification::class);
    }
}
