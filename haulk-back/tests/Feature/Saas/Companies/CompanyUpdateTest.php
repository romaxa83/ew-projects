<?php

namespace Tests\Feature\Saas\Companies;

use App\Models\Locations\State;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\TestCase;

class CompanyUpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected CompanyBuilder $companyBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    public function test_can_update_company_info(): void
    {
        $this->loginAsSaasSuperAdmin();

        $createData = [
            'usdot' => 123456,
            'name' => 'testSomeCompanyName',
            'email' => 'testUnique@email.com',
        ];

        $company = Company::factory()->create($createData);

        $this->assertDatabaseHas('companies', $createData);

        $state = State::first() ? State::first() : factory(State::class)->create();

        $updateData = [
            'email' => 'testUnique1@email.com',
            'address' => 'address',
            'city' => 'city',
            'state_id' => $state->id,
            'zip' => '32315',
            'phone' => '1234567899',
        ];

        $this->putJson(route('v1.saas.companies.update', $company['id']), $updateData)
            ->assertOk();

        $this->assertDatabaseHas('companies', $updateData);
        $this->assertDatabaseMissing('companies', $createData);
    }

    public function test_can_update_use_in_body_shop_column(): void
    {
        $this->loginAsSaasSuperAdmin();

        $createData = [
            'usdot' => 123456,
            'name' => 'testSomeCompanyName',
            'email' => 'testUnique@email.com',
            'use_in_body_shop' => false,
        ];

        $company = Company::factory()->create($createData);

        $this->assertDatabaseHas('companies', $createData);

        $state = State::first() ? State::first() : factory(State::class)->create();

        $updateData = [
            'email' => 'testUnique1@email.com',
            'address' => 'address',
            'city' => 'city',
            'state_id' => $state->id,
            'zip' => '32315',
            'phone' => '1234567899',
            'use_in_body_shop' => true,
        ];

        $this->putJson(route('v1.saas.companies.update', $company['id']), $updateData)
            ->assertOk();

        $this->assertDatabaseHas('companies', $updateData);
        $this->assertDatabaseMissing('companies', $createData);
    }

    /** @test */
    public function update_next_rate(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $deviceSubscription DeviceSubscription */
        $deviceSubscription = $this->deviceSubscriptionsBuilder->company($company)->create();

        $state = State::first() ? State::first() : factory(State::class)->create();

        $updateData = [
            'email' => 'testUnique1@email.com',
            'address' => 'address',
            'city' => 'city',
            'state_id' => $state->id,
            'zip' => '32315',
            'phone' => '1234567899',
            'next_rate' => 10,
        ];

        $this->assertNull($company->gpsDeviceSubscription->next_rate);

        $this->putJson(route('v1.saas.companies.update', $company['id']), $updateData)
            ->assertJson([
                'data' => [
                    'id' => $company->id,
                    'gps_subscription' => [
                        'id' => $deviceSubscription->id,
                        'next_rate' => data_get($updateData, 'next_rate')
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_next_rate_and_current_rate_same(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $deviceSubscription DeviceSubscription */
        $deviceSubscription = $this->deviceSubscriptionsBuilder
            ->currentRate(10)
            ->company($company)->create();

        $state = State::first() ? State::first() : factory(State::class)->create();

        $updateData = [
            'email' => 'testUnique1@email.com',
            'address' => 'address',
            'city' => 'city',
            'state_id' => $state->id,
            'zip' => '32315',
            'phone' => '1234567899',
            'next_rate' => 10,
        ];

        $res = $this->putJson(route('v1.saas.companies.update', $company['id']), $updateData);

        $this->assertResponseHasValidationMessage($res, 'next_rate',
            __('validation.custom.gps.device_subscription.next_rate_same_current_rate')
        );
    }

    /** @test */
    public function fail_not_has_device_subscription(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->create();


        $state = State::first() ? State::first() : factory(State::class)->create();

        $updateData = [
            'email' => 'testUnique1@email.com',
            'address' => 'address',
            'city' => 'city',
            'state_id' => $state->id,
            'zip' => '32315',
            'phone' => '1234567899',
            'next_rate' => 10,
        ];

        $res = $this->putJson(route('v1.saas.companies.update', $company['id']), $updateData)
        ;

        $this->assertResponseHasValidationMessage($res, 'next_rate',
            __('validation.custom.gps.device_subscription.field_for_device_subscription', [
                'field' =>'next_rate'
            ])
        );
    }
}
