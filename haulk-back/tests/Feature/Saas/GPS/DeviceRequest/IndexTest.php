<?php

namespace Tests\Feature\Saas\GPS\DeviceRequest;

use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceRequestBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

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
    public function success_list(): void
    {
        $this->loginAsSaasSuperAdmin();

        $user = $this->userBuilder->asDriver()->create();

        $this->deviceRequestBuilder->user($user)->create();
        $this->deviceRequestBuilder->user($user)->create();
        $this->deviceRequestBuilder->user($user)->create();

        $this->getJson(route('v1.saas.gps-devices.request'))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'to' => 3,
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_list_page(): void
    {
        $this->loginAsSaasSuperAdmin();

        $user = $this->userBuilder->asDriver()->create();

        $this->deviceRequestBuilder->user($user)->create();
        $this->deviceRequestBuilder->user($user)->create();
        $this->deviceRequestBuilder->user($user)->create();

        $this->getJson(route('v1.saas.gps-devices.request', [
            'page' => 2
        ]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'from' => null,
                    'last_page' => 1,
                    'to' => null,
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_list_per_page(): void
    {
        $this->loginAsSaasSuperAdmin();

        $user = $this->userBuilder->asDriver()->create();

        $this->deviceRequestBuilder->user($user)->create();
        $this->deviceRequestBuilder->user($user)->create();
        $this->deviceRequestBuilder->user($user)->create();

        $this->getJson(route('v1.saas.gps-devices.request', [
            'per_page' => 1
        ]))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 3,
                    'to' => 1,
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->getJson(route('v1.saas.gps-devices.request'))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'last_page' => 1,
                    'to' => null,
                    'total' => 0,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_status(): void
    {
        $this->loginAsSaasSuperAdmin();

        $user = $this->userBuilder->asDriver()->create();

        $this->deviceRequestBuilder->user($user)->status(DeviceRequestStatus::CLOSED())->create();
        $this->deviceRequestBuilder->user($user)->create();
        $this->deviceRequestBuilder->user($user)->create();

        $this->getJson(route('v1.saas.gps-devices.request', [
            'status' => DeviceRequestStatus::CLOSED
        ]))
            ->assertJson([
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_company(): void
    {
        $this->loginAsSaasSuperAdmin();

        $user = $this->userBuilder->asDriver()->create();

        $company = $this->companyBuilder->create();

        $this->deviceRequestBuilder->user($user)->company($company)->create();
        $this->deviceRequestBuilder->user($user)->company($company)->create();
        $this->deviceRequestBuilder->user($user)->create();

        $this->getJson(route('v1.saas.gps-devices.request', [
            'company_id' => $company->id
        ]))
            ->assertJson([
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsSaasAdmin();

        $company = $this->companyBuilder->create();

        $res = $this->getJson(route('v1.saas.gps-devices.request', [
            'company_id' => $company->id
        ]))
        ;

        $this->assertResponseUnauthorizedMessage($res);
    }

}

