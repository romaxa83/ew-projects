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

class ListFreeTest extends TestCase
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
    public function success_list_without_truck(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();
        $m_2 = $this->deviceBuilder->company($company)->create();
        $m_3 = $this->deviceBuilder->create();

        $this->truckBuilder->device($m_1)->create();

        $this->getJson(route('gps.device-index-api'))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id]
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_without_trailer(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();
        $user = $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();
        $m_2 = $this->deviceBuilder->company($company)->create();
        $m_3 = $this->deviceBuilder->create();

        $this->trailerBuilder->device($m_1)->create();
        $this->getJson(route('gps.device-index-api'))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id]
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_without_trailer_and_truck(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();
        $user = $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();
        $m_2 = $this->deviceBuilder->company($company)->create();
        $m_3 = $this->deviceBuilder->company($company)->create();
        $m_4 = $this->deviceBuilder->create();

        $this->trailerBuilder->device($m_1)->create();
        $this->truckBuilder->device($m_2)->create();
        $this->getJson(route('gps.device-index-api'))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id]
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_filter_device_id(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();
        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();
        $m_2 = $this->deviceBuilder->company($company)->create();
        $m_3 = $this->deviceBuilder->company($company)->create();
        $m_4 = $this->deviceBuilder->create();

        $this->trailerBuilder->device($m_1)->create();
        $this->truckBuilder->device($m_2)->create();


        $this->getJson(route('gps.device-index-api', ['with_device_id' => $m_2->id]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_3->id]
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_filter_device_id_only_active(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();
        $this->loginAsCarrierSuperAdmin($user);

        $m_1 = $this->deviceBuilder->company($company)->create();
        $m_2 = $this->deviceBuilder->company($company)->create();
        $m_3 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::INACTIVE())->create();
        $m_4 = $this->deviceBuilder->create();

        $this->trailerBuilder->device($m_1)->create();
        $this->truckBuilder->device($m_2)->create();


        $this->getJson(route('gps.device-index-api', [
            'with_device_id' => $m_2->id,
            'status' => 'active'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id]
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }
}


