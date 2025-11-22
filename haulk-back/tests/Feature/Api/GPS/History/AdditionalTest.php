<?php

namespace Tests\Feature\Api\GPS\History;

use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\AlertBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\HistoryBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class AdditionalTest extends TestCase
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
    protected HistoryBuilder $historyBuilder;
    protected AlertBuilder $alertBuilder;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
        $this->alertBuilder = resolve(AlertBuilder::class);

    }

    /** @test */
    public function success_total(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $driver_1 = $this->userBuilder->asDriver()->create();
        $driver_2 = $this->userBuilder->asDriver()->create();

        $h_1 = $this->historyBuilder->company($company)
            ->driver($driver_1)
            ->mileage(1.5)
            ->receivedAt($date->subMinutes(10))->create();
        $h_2 = $this->historyBuilder->company($company)
            ->mileage(2.5)
            ->receivedAt($date->subMinutes(9))->create();
        $h_3 = $this->historyBuilder->company($company)
            ->driver($driver_2)
            ->mileage(3)
            ->receivedAt($date->subMinutes(8))->create();
        $h_4 = $this->historyBuilder->receivedAt($date->subMinutes(10))->create();

        $this->getJson(route('gps.gps-history-additional'))
            ->assertJson([
                'data' => [
                    'total_mileage' => 1.5,
                    'drivers' => [
                        [
                            'id' => $driver_1->id,
                            'full_name' => $driver_1->full_name,
                        ],
                        [
                            'id' => $driver_2->id,
                            'full_name' => $driver_2->full_name,
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_total_as_null(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $h_1 = $this->historyBuilder->company($company)
            ->receivedAt($date->subMinutes(10))->create();
        $h_2 = $this->historyBuilder->company($company)
            ->receivedAt($date->subMinutes(9))->create();
        $h_3 = $this->historyBuilder->company($company)
            ->receivedAt($date->subMinutes(8))->create();
        $h_4 = $this->historyBuilder->receivedAt($date->subMinutes(10))->create();

        $this->getJson(route('gps.gps-history-additional'))
            ->assertJson([
                'data' => [
                    'total_mileage' => 0,
                    'drivers' => [],
                ]
            ])
        ;
    }

    /** @test */
    public function fail_total_no_data(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDriver()
            ->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $this->getJson(route('gps.gps-history-additional'))
            ->assertJson([
                'data' => [
                    'total_mileage' => 0,
                    'drivers' => [],
                ]
            ])
        ;
    }
}

