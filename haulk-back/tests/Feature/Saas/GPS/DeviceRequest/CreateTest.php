<?php

namespace Tests\Feature\Saas\GPS\DeviceRequest;

use App\Enums\Saas\GPS\Request\DeviceRequestSource;
use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceRequestBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

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
    public function success_create(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDispatcher()->company($company)->create();

        $this->assertNull($company->gpsDeviceSubscription);

        $data = [
            'qty' => 10,
            'company_id' => $company->id,
            'user_email' => $user->email
        ];

        $this->postJson(route('v1.saas.gps-devices.request-create'), $data)
            ->assertJson([
                'data' => [
                    'status' => DeviceRequestStatus::NEW(),
                    'qty' => $data['qty'],
                    'source' => DeviceRequestSource::BACKOFFICE,
                    'company' => [
                        'id' => $company->id
                    ],
                    'user' => [
                        'id' => $user->id
                    ]
                ]
            ])
        ;

        $company->refresh();
        $this->assertNotNull($company->gpsDeviceSubscription);
    }

    /** @test */
    public function fail_create_user_does_not_belongs_to_company(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asDispatcher()->company($company_2)->create();

        $data = [
            'qty' => 10,
            'company_id' => $company->id,
            'user_email' => $user->email
        ];

        $res = $this->postJson(route('v1.saas.gps-devices.request-create'), $data)
        ;

        $this->assertResponseHasValidationMessage($res, 'user_email',
            __('validation.custom.user.not_belongs_company')
        );
    }
}

