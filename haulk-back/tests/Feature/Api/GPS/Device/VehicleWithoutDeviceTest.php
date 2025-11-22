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

class VehicleWithoutDeviceTest extends TestCase
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
    public function success_list(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $d_1 = $this->deviceBuilder->company($company)->create();
        $d_2 = $this->deviceBuilder->company($company)->create();

        $this->truckBuilder->company($company)->device($d_1)->create();
        $truck = $this->truckBuilder->company($company)->create();

        $trailer = $this->trailerBuilder->company($company)->create();
        $this->trailerBuilder->company($company)->device($d_2)->create();

        $this->getJson(route('gps.device.vehicle-without-device'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $truck->id,
                        'is_truck' => true
                    ],
                    [
                        'id' => $trailer->id,
                        'is_truck' => false
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }


    public function success_list_search(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();

        /** @var $user User */
        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $d_1 = $this->deviceBuilder->company($company)->create();
        $d_2 = $this->deviceBuilder->company($company)->create();

        $this->truckBuilder->company($company)->device($d_1)->create();
        $truck = $this->truckBuilder->company($company)->unitNumber('1111879')->create();

//        dd($truck);

        $trailer = $this->trailerBuilder->company($company)->create();
        $trailer_2 = $this->trailerBuilder->company($company)->vin('1111879867898')->create();
        $this->trailerBuilder->company($company)->device($d_2)->create();

        $this->getJson(route('gps.device.vehicle-without-device'),[
            'search' => '111'
        ])
            ->dump()
            ->assertJson([
                'data' => [
                    [
                        'id' => $truck->id,
                        'is_truck' => true
                    ],
                    [
                        'id' => $trailer->id,
                        'is_truck' => false
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }
}

