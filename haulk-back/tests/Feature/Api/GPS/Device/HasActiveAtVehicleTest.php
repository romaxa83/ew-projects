<?php

namespace Tests\Feature\Api\GPS\Device;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class HasActiveAtVehicleTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected UserBuilder $userBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
    }

    /** @test */
    public function success_active_truck(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();

        $this->truckBuilder->device($m_1)->create();

        $this->getJson(route('gps.device.has-active-at-vehicle'))
            ->assertJson([
                'data' => true
            ])
        ;
    }

    /** @test */
    public function success_active_trailer(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();

        $this->trailerBuilder->device($m_1)->create();

        $this->getJson(route('gps.device.has-active-at-vehicle'))
            ->assertJson([
                'data' => true
            ])
        ;
    }

    /** @test */
    public function fail_empty(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $this->getJson(route('gps.device.has-active-at-vehicle'))
            ->assertJson([
                'data' => false
            ])
        ;
    }

    /** @test */
    public function fail_inactive_truck(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->status(DeviceStatus::INACTIVE())->create();
        $m_2 = $this->deviceBuilder->company($company)->create();

        $this->truckBuilder->device($m_1)->create();

        $this->getJson(route('gps.device.has-active-at-vehicle'))
            ->assertJson([
                'data' => false
            ])
        ;
    }

    /** @test */
    public function fail_inactive_trailer(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->status(DeviceStatus::INACTIVE())->create();
        $m_2 = $this->deviceBuilder->company($company)->create();

        $this->trailerBuilder->device($m_1)->create();

        $this->getJson(route('gps.device.has-active-at-vehicle'))
            ->assertJson([
                'data' => false
            ])
        ;
    }

    /** @test */
    public function fail_not_attach(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();
        $m_2 = $this->deviceBuilder->company($company)->create();

        $this->getJson(route('gps.device.has-active-at-vehicle'))
            ->assertJson([
                'data' => false
            ])
        ;
    }
}
