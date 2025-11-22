<?php

namespace Tests\Feature\Saas\Companies;

use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CompaniesListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_not_permitted_admin_can_not_view_companies(): void
    {
        $this->loginAsSaasAdmin();

        $this->getJson(route('v1.saas.companies.index'))
            ->assertForbidden();
    }

    public function test_permitted_admin_can_view_companies(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->getJson(route('v1.saas.companies.index'))->assertOk();
    }

    public function test_gps_devices_count_filed(): void
    {
        $this->loginAsSaasSuperAdmin();

        $company = Company::factory(['gps_enabled' => true])->create();
        Device::factory(['company_id' => $company->id])->create();
        Device::factory(['company_id' => $company->id])->create();

        $response = $this->getJson(route('v1.saas.companies.index'))
            ->assertOk();

        $this->assertEquals(0, $response['data'][0]['gps_devices_count']);
        $this->assertEquals(2, $response['data'][1]['gps_devices_count']);
    }
}
