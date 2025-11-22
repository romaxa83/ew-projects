<?php

namespace Tests\Feature\Api\GPS\Device;

use App\Enums\Notifications\NotificationPlace;
use App\Enums\Notifications\NotificationStatus;
use App\Enums\Notifications\NotificationType;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Models\Notifications\Notification;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Billing\InvoiceBuilder;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class ToggleActivateTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected UserBuilder $userBuilder;
    protected InvoiceBuilder $invoiceBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->invoiceBuilder = resolve(InvoiceBuilder::class);
    }

    /** @test */
    public function success_activate(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model Device */
        $model = $this->deviceBuilder
            ->company($company)
            ->status(DeviceStatus::INACTIVE())
            ->create();

        $this->assertFalse(Notification::query()->exists());

        $this->assertTrue($model->status_request->isNone());
        $this->assertTrue($model->status_activate_request->isNone());
        $this->assertNull($model->send_request_user_id);

        $this->putJson(route('gps.device-toggle-activate-api', [$model]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => DeviceStatus::INACTIVE(),
                    'status_activate_request' => DeviceStatusActivateRequest::ACTIVATE,
                    'status_request' => DeviceRequestStatus::PENDING,
                    'active_at' => null,
                    'inactive_at' => null,
                ]
            ])
        ;

        $model->refresh();
        $this->assertEquals($model->send_request_user_id, $user->id);

        /** @var $notification Notification */
        $notification = Notification::query()
            ->where('status', NotificationStatus::NEW())
            ->where('type', NotificationType::GPS())
            ->where('place', NotificationPlace::BACKOFFICE())
            ->first();

        $this->assertEquals($notification->message_key, 'notification.device.request_activate');
        $this->assertEquals($notification->message_attr, [
            'company_name' => $company->name,
            'imei' => $model->imei
        ]);

        $this->assertEquals($notification->meta, [
            'device_id' => $model->id,
            'device_status' => $model->status,
            'device_request_status' => $model->status_request,
            'company_id' => $company->id,
        ]);
    }

    /** @test */
    public function success_deactivate(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model Device */
        $model = $this->deviceBuilder
            ->company($company)
            ->status(DeviceStatus::ACTIVE())
            ->create();

        $this->assertFalse(Notification::query()->exists());

        $this->assertTrue($model->status_request->isNone());
        $this->assertTrue($model->status_activate_request->isNone());
        $this->assertNull($model->send_request_user_id);

        $this->putJson(route('gps.device-toggle-activate-api', [$model]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => DeviceStatus::ACTIVE(),
                    'status_activate_request' => DeviceStatusActivateRequest::DEACTIVATE,
                    'status_request' => DeviceRequestStatus::PENDING,
                    'active_at' => null,
                    'inactive_at' => null,
                ]
            ])
        ;

        $model->refresh();
        $this->assertEquals($model->send_request_user_id, $user->id);

        /** @var $notification Notification */
        $notification = Notification::query()
            ->where('status', NotificationStatus::NEW())
            ->where('type', NotificationType::GPS())
            ->where('place', NotificationPlace::BACKOFFICE())
            ->first();

        $this->assertEquals($notification->message_key, 'notification.device.request_deactivate');
        $this->assertEquals($notification->message_attr, [
            'company_name' => $company->name,
            'imei' => $model->imei
        ]);
        $this->assertEquals($notification->meta, [
            'device_id' => $model->id,
            'device_status' => $model->status,
            'device_request_status' => $model->status_request,
            'company_id' => $company->id,
        ]);
    }

    /** @test */
    public function fail_activate_device_deleted(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model Device */
        $model = $this->deviceBuilder
            ->company($company)
            ->status(DeviceStatus::DELETED())
            ->create();

        $res = $this->putJson(route('gps.device-toggle-activate-api', [$model]))
        ;

        $this->assertResponseHasValidationMessage($res, 'id',
            __("exceptions.gps_device.cant_toggle_because_delete")
        );
    }

    /** @test */
    public function fail_activate_has_unpaid_invoice(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder
            ->trial($this->companyBuilder->create());
        $this->invoiceBuilder->company($company)->unpaid()->create();

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model Device */
        $model = $this->deviceBuilder
            ->company($company)
            ->status(DeviceStatus::INACTIVE())
            ->create();

        $this->assertTrue($company->hasUnpaidInvoices());

        $res = $this->putJson(route('gps.device-toggle-activate-api', [$model]))
        ;

        $this->assertResponseErrorMessage($res, __('exceptions.company.billing.has_unpaid_invoice', [
            'company_name' => $company->name
        ]), 401);
    }
}
