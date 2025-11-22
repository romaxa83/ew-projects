<?php

namespace Tests\Feature\Saas\Companies;

use App\Models\Saas\Company\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CompanyDeleteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_not_delete_active_company(): void
    {
        $this->loginAsSaasSuperAdmin();

        $company = $this->getCompany();

        $company->update(['active' => true]);

        $this->postJson(route('v1.saas.companies.send-destroy-notification', ['company' => $company]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_activate_company_after_send_note()
    {
        $this->loginAsSaasSuperAdmin();

        $company = $this->getCompany();
        $company = $this->sendCompanyDestroyNote($company);

        $this->putJson(route('v1.saas.companies.status', ['company' => $company]));

        $company->refresh();

        $this->assertTrue($company->active);
        $this->assertNull($company->saas_confirm_token);
        $this->assertNull($company->saas_decline_token);
        $this->assertNull($company->saas_date_token_create);
        $this->assertNull($company->saas_date_delete);
    }

    public function test_incorrect_decline_token()
    {
        $this->loginAsSaasSuperAdmin();

        $company = $this->getCompany();
        $company = $this->sendCompanyDestroyNote($company);

        $this->postJson(
            route('v1.saas.companies.set-destroy'),
            [
                'type' => 'decline',
                'token' => $company->saas_confirm_token
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $company->refresh();
        $this->assertNotNull($company->saas_confirm_token);
        $this->assertNotNull($company->saas_decline_token);
        $this->assertNotNull($company->saas_date_token_create);
        $this->assertNull($company->saas_date_delete);
    }

    public function test_decline_token()
    {
        $this->loginAsSaasSuperAdmin();

        $company = $this->getCompany();
        $company = $this->sendCompanyDestroyNote($company);

        $this->postJson(
            route('v1.saas.companies.set-destroy'),
            [
                'type' => 'decline',
                'token' => $company->saas_decline_token
            ]
        )->assertNoContent();

        $company->refresh();
        $this->assertNull($company->saas_confirm_token);
        $this->assertNull($company->saas_decline_token);
        $this->assertNull($company->saas_date_token_create);
        $this->assertNull($company->saas_date_delete);
    }

    public function test_incorrect_confirm_token()
    {
        $this->loginAsSaasSuperAdmin();

        $company = $this->getCompany();
        $company = $this->sendCompanyDestroyNote($company);

        $this->postJson(
            route('v1.saas.companies.set-destroy'),
            [
                'type' => 'confirm',
                'token' => $company->saas_decline_token
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $company->refresh();
        $this->assertNotNull($company->saas_confirm_token);
        $this->assertNotNull($company->saas_decline_token);
        $this->assertNotNull($company->saas_date_token_create);
        $this->assertNull($company->saas_date_delete);
    }

    public function test_confirm_token(): Company
    {
        $this->loginAsSaasSuperAdmin();

        $company = $this->getCompany();
        $company = $this->sendCompanyDestroyNote($company);

        $this->postJson(
            route('v1.saas.companies.set-destroy'),
            [
                'type' => 'confirm',
                'token' => $company->saas_confirm_token
            ]
        )->assertNoContent();

        $company->refresh();
        $this->assertNull($company->saas_confirm_token);
        $this->assertNull($company->saas_decline_token);
        $this->assertNull($company->saas_date_token_create);
        $this->assertNotNull($company->saas_date_delete);

        $this->assertEquals($company->saas_date_delete, Carbon::now()->addMonth()->toDateString());

        return $company;
    }

    public function test_activate_company_after_confirm_delete()
    {
        $company = $this->test_confirm_token();

        $this->putJson(route('v1.saas.companies.status', ['company' => $company]));

        $company->refresh();

        $this->assertTrue($company->active);
        $this->assertNull($company->saas_confirm_token);
        $this->assertNull($company->saas_decline_token);
        $this->assertNull($company->saas_date_token_create);
        $this->assertNull($company->saas_date_delete);
    }

    private function getCompany(): Company
    {
        $createData = [
            'active' => false,
            'saas_confirm_token' => null,
            'saas_decline_token' => null,
            'saas_date_token_create' => null,
            'saas_date_delete' => null
        ];

        $company = Company::factory()->create($createData);

        $this->assertDatabaseHas('companies', $createData);

        return $company;
    }

    private function sendCompanyDestroyNote(Company $company): Company
    {
        $company->refresh();

        $this->assertFalse($company->active);

        $this->postJson(route('v1.saas.companies.send-destroy-notification', ['company' => $company]))
            ->assertNoContent();

        $company->refresh();
        $this->assertNotNull($company->saas_confirm_token);
        $this->assertNotNull($company->saas_decline_token);
        $this->assertNotNull($company->saas_date_token_create);
        $this->assertNull($company->saas_date_delete);
        return $company;
    }
}
