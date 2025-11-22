<?php

namespace Tests\Feature\Saas\Companies;

use App\Models\Admins\Admin;
use App\Models\Saas\Company\Company;
use App\Models\Saas\Company\CompanySetting;
use App\Models\Saas\CompanyRegistration\CompanyRegistration;
use App\Models\Users\User;
use App\Permissions\Saas\CompanyRegistration\CompanyRegistrationApprove;
use App\Permissions\Saas\CompanyRegistration\CompanyRegistrationDecline;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class CompanyRegistrationTest extends TestCase
{
    use AdminFactory;
    use DatabaseTransactions;
    use PermissionFactory;

    private $companyData = [
        'usdot' => 123456,
        'ga_id' => 'test_id_test_id',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'email@email.email',
        'password' => 'password1',
        'password_confirmation' => 'password1',
    ];
    public function test_new_carrier_registration_request(): void
    {
        $this->postJson(
            route('v1.saas.company-registration.registration-request'),
            $this->companyData
        )
            ->assertOk();

        $this->assertDatabaseHas(
            CompanyRegistration::TABLE,
            [
                'usdot' => $this->companyData['usdot'],
                'ga_id' => $this->companyData['ga_id'],
                'confirmed' => false,
            ]
        );
    }
    public function test_list_company_user(): void
    {
        $this->postJson(
            route('v1.saas.company-registration.registration-request'),
            $this->companyData
        )
            ->assertOk();

        $this->assertDatabaseHas(
            CompanyRegistration::TABLE,
            [
                'usdot' => $this->companyData['usdot'],
                'ga_id' => $this->companyData['ga_id'],
                'confirmed' => false,
            ]
        );
    }
    public function test_confirm_registration_email(): void
    {
        $this->postJson(
            route('v1.saas.company-registration.registration-request'),
            $this->companyData
        )
            ->assertOk();
        $companyRegistration = CompanyRegistration::where('usdot', $this->companyData['usdot'])->first();
        $confirmation_hash = \Str::random(60);

        $companyRegistration->confirmation_hash = hash('sha256', $confirmation_hash);
        $companyRegistration->save();

        $this->postJson(
            route('v1.saas.company-registration.confirm-registration-email'),
            ['confirmation_hash' => $confirmation_hash]
        )
            ->assertOk();

        $this->assertDatabaseMissing(
            CompanyRegistration::TABLE,
            [
                'usdot' => $this->companyData['usdot'],
            ]
        );

        $this->assertDatabaseHas(
            Company::TABLE_NAME,
            [
                'usdot' => $this->companyData['usdot'],
                'active' => true,
            ]
        );

        $this->assertDatabaseHas(
            User::TABLE_NAME,
            [
                'status' => User::STATUS_ACTIVE,
                'email' => $companyRegistration->email,
            ]
        );
    }
    public function test_decline_carrier_registration_request(): void
    {
        $this->postJson(
            route('v1.saas.company-registration.registration-request'),
            $this->companyData
        )
            ->assertOk();

        $this->assertDatabaseHas(
            CompanyRegistration::TABLE,
            [
                'usdot' => 123456,
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'email' => 'email@email.email',
            ]
        );

        $companyRegistration = CompanyRegistration::where('usdot', $this->companyData['usdot'])->first();
        $companyRegistration->confirmed = true;
        $companyRegistration->save();

        $admin = $this->createAdmin()->assignRole(
            $this->createRole('Admin', [CompanyRegistrationDecline::KEY], Admin::GUARD)
        );

        $this->loginAsSaasAdmin($admin);

        $this->putJson(
            route(
                'v1.saas.company-registration.decline',
                $companyRegistration
            )
        )
            ->assertOk();

        $this->assertDatabaseMissing(
            CompanyRegistration::TABLE,
            [
                'usdot' => $this->companyData['usdot'],
            ]
        );
    }
    public function test_approve_carrier_registration_request(): void
    {
        $this->postJson(
            route('v1.saas.company-registration.registration-request'),
            $this->companyData
        )
            ->assertOk();

        $companyRegistration = CompanyRegistration::where('usdot', $this->companyData['usdot'])->first();
        $companyRegistration->confirmed = true;
        $companyRegistration->save();

        $admin = $this->createAdmin()->assignRole(
            $this->createRole('Admin', [CompanyRegistrationApprove::KEY], Admin::GUARD)
        );

        $this->loginAsSaasAdmin($admin);

        $this->putJson(
            route(
                'v1.saas.company-registration.approve',
                $companyRegistration
            )
        )
            ->assertOk();

        $this->assertDatabaseMissing(
            CompanyRegistration::TABLE,
            [
                'usdot' => $this->companyData['usdot'],
                'ga_id' => $this->companyData['ga_id'],
            ]
        );

        $this->assertDatabaseHas(
            Company::TABLE_NAME,
            [
                'usdot' => $this->companyData['usdot'],
                'email' => $this->companyData['email'],
                'ga_id' => $this->companyData['ga_id'],
                'registration_at' => $companyRegistration->created_at,
            ]
        );

        $this->assertDatabaseHas(
            User::TABLE_NAME,
            [
                'email' => $this->companyData['email'],
            ]
        );
        $company = Company::where('usdot', $this->companyData['usdot'])
            ->where('email', $this->companyData['email'])
            ->first();

        $this->assertDatabaseHas(
            CompanySetting::TABLE,
            [
                'company_id' => $company->id
            ]
        );
    }
}
