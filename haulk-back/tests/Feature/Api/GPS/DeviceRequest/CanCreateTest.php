<?php

namespace Feature\Api\GPS\DeviceRequest;

use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceRequestBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class CanCreateTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;
    protected DeviceRequestBuilder $deviceRequestBuilder;
    protected CompanyBuilder $companyBuilder;
    protected UserBuilder $userBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceRequestBuilder = resolve(DeviceRequestBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function can_create(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model DeviceRequest */
        $model = $this->deviceRequestBuilder
            ->status(DeviceRequestStatus::CLOSED())
            ->company($company)
            ->create();
        /** @var $model DeviceRequest */
        $model_1 = $this->deviceRequestBuilder
            ->status(DeviceRequestStatus::CLOSED())
            ->company($company)
            ->create();

        $this->getJson(route('gps.device.request.can-create'))
            ->assertJson([
                'data' => true
            ])
        ;
    }

    /** @test */
    public function can_create_not_record(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $this->getJson(route('gps.device.request.can-create'))
            ->assertJson([
                'data' => true
            ])
        ;
    }

    /** @test */
    public function can_not_create_status_new(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $model_1 = $this->deviceRequestBuilder
            ->status(DeviceRequestStatus::CLOSED())
            ->company($company)
            ->create();

        /** @var $model DeviceRequest */
        $model = $this->deviceRequestBuilder
            ->status(DeviceRequestStatus::NEW())
            ->company($company)
            ->create();


        $this->getJson(route('gps.device.request.can-create'))
            ->assertJson([
                'data' => false
            ])
        ;
    }

    /** @test */
    public function can_not_create_status_in_work(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model DeviceRequest */
        $model = $this->deviceRequestBuilder
            ->status(DeviceRequestStatus::IN_WORK())
            ->company($company)
            ->create();

        $this->getJson(route('gps.device.request.can-create'))
            ->assertJson([
                'data' => false
            ])
        ;
    }
}



