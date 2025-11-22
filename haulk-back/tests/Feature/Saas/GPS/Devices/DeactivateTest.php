<?php

namespace Tests\Feature\Saas\GPS\Devices;

use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class DeactivateTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected CompanyBuilder $companyBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected UserBuilder $userBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_deactivate(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        $userSendRequest = $this->userBuilder->asDispatcher()->company($company)->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::INACTIVE())
            ->statusRequest(DeviceRequestStatus::CANCEL_SUBSCRIPTION())
            ->statusActivateRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->sendRequestUser($userSendRequest)
            ->create();

        $this->deviceSubscriptionsBuilder->company($company)
            ->status(DeviceSubscriptionStatus::CANCELED())->create();

        $this->putJson(route('v1.saas.gps-devices.deactivate', [$model]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status_request' => DeviceRequestStatus::CLOSED,
                    'status_activate_request' => DeviceStatusActivateRequest::NONE,
                    'active_till_at' => null,
                    'status' => DeviceStatus::INACTIVE,
                ]
            ]);
    }
}



