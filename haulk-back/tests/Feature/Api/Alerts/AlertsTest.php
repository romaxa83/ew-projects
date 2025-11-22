<?php


namespace Api\Alerts;


use App\Broadcasting\Events\Alerts\CompanyAlertBroadcast;
use App\Broadcasting\Events\Alerts\UserAlertBroadcast;
use App\Models\Alerts\Alert;
use App\Models\Alerts\DeletedAlert;
use App\Notifications\Alerts\AlertNotification;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Notification;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class AlertsTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_company_alert_created(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $message = 'notification text';

        $this->assertDatabaseMissing(
            Alert::class,
            [
                'recipient_id' => null,
                'message' => $message,
            ]
        );

        Notification::send(
            $superadmin->getCompany(),
            new AlertNotification(
                $superadmin->getCompanyId(),
                $message
            )
        );

        $this->assertDatabaseHas(
            Alert::class,
            [
                'recipient_id' => null,
                'message' => $message,
            ]
        );
    }

    public function test_company_alert_created_broadcasts(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $message = 'notification text';

        Event::fake();

        Notification::send(
            $superadmin->getCompany(),
            new AlertNotification(
                $superadmin->getCompanyId(),
                $message
            )
        );

        Event::assertDispatched(CompanyAlertBroadcast::class);
    }

    public function test_user_alert_created(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $message = 'notification text';
        $driver = $this->driverFactory();

        $this->assertDatabaseMissing(
            Alert::class,
            [
                'recipient_id' => $driver->id,
                'message' => $message,
            ]
        );

        $driver->notify(
            new AlertNotification(
                $driver->getCompanyId(),
                $message
            )
        );

        $this->assertDatabaseHas(
            Alert::class,
            [
                'recipient_id' => $driver->id,
                'message' => $message,
            ]
        );
    }

    public function test_user_alert_created_broadcasts(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $message = 'notification text';
        $driver = $this->driverFactory();

        Event::fake();

        $driver->notify(
            new AlertNotification(
                $driver->getCompanyId(),
                $message
            )
        );

        Event::assertDispatched(UserAlertBroadcast::class);
    }

    public function test_recent_alerts_list(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->assertDatabaseMissing(
            Alert::class,
            [
                'type' => AlertNotification::TARGET_TYPE_NEWS,
            ]
        );

        $this->postJson(
            route('news.store'),
            [
                'title_en' => 'title_en',
                'body_en' => 'body_en',
            ]
        )
            ->assertCreated();

        $this->getJson(
            route(
                'alerts.index',
                [
                    'newer_than' => now()->subDay()->timestamp,
                ]
            )
        )
            ->assertJsonPath('data.0.type', AlertNotification::TARGET_TYPE_NEWS);
    }

    public function test_company_alert_deleted(): void
    {
        $user = $this->loginAsCarrierSuperAdmin();

        $this->assertDatabaseMissing(
            Alert::class,
            [
                'type' => AlertNotification::TARGET_TYPE_NEWS,
            ]
        );

        $this->assertDatabaseMissing(
            DeletedAlert::class,
            [
                'user_id' => $user->id,
            ]
        );

        $this->postJson(
            route('news.store'),
            [
                'title_en' => 'title_en',
                'body_en' => 'body_en',
            ]
        )
            ->assertCreated();

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertNotification::TARGET_TYPE_NEWS,
            ]
        );

        $response = $this->getJson(
            route(
                'alerts.index',
                [
                    'newer_than' => now()->subDay()->timestamp,
                ]
            )
        )
            ->assertOk();

        $this->deleteJson(route('alerts.destroy', $response->json('data')[0]['id']))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertNotification::TARGET_TYPE_NEWS,
            ]
        );

        $this->assertDatabaseHas(
            DeletedAlert::class,
            [
                'alert_id' => $response->json('data')[0]['id'],
                'user_id' => $user->id,
            ]
        );

        $response = $this->getJson(
            route(
                'alerts.index',
                [
                    'newer_than' => now()->subDay()->timestamp,
                ]
            )
        )
            ->assertOk();

        $this->assertCount(0, $response->json('data'));
    }

    public function test_user_alert_deleted(): void
    {
        $user = $this->loginAsCarrierSuperAdmin();

        $this->assertDatabaseMissing(
            Alert::class,
            [
                'recipient_id' => $user->id,
            ]
        );

        $user->notify(
            new AlertNotification(
                $user->getCompanyId(),
                'test message'
            )
        );

        $this->assertDatabaseHas(
            Alert::class,
            [
                'recipient_id' => $user->id,
            ]
        );

        $response = $this->getJson(
            route(
                'alerts.index',
                [
                    'newer_than' => now()->subDay()->timestamp,
                ]
            )
        )
            ->assertOk();

        $this->deleteJson(route('alerts.destroy', $response->json('data')[0]['id']))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            Alert::class,
            [
                'recipient_id' => $user->id,
            ]
        );

        $response = $this->getJson(
            route(
                'alerts.index',
                [
                    'newer_than' => now()->subDay()->timestamp,
                ]
            )
        )
            ->assertOk();

        $this->assertCount(0, $response->json('data'));
    }
}
