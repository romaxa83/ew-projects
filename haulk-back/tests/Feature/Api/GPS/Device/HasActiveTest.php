<?php

namespace Tests\Feature\Api\GPS\Device;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class HasActiveTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected UserBuilder $userBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_has_active(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $this->deviceBuilder->company($company)->create();

        $this->getJson(route('gps.device.has-active'))
            ->assertJson([
                'data' => true
            ])
        ;
    }

    /** @test */
    public function fail_not_has_active(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $this->deviceBuilder->company($company)->status(DeviceStatus::INACTIVE())->create();
        $this->deviceBuilder->company($company)->status(DeviceStatus::DELETED())->create();

        $this->getJson(route('gps.device.has-active'))
            ->assertJson([
                'data' => false
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

        $this->getJson(route('gps.device.has-active'))
            ->assertJson([
                'data' => false
            ])
        ;
    }
}

