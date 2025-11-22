<?php

namespace Tests\Feature\Saas\Companies;

use App\Models\Admins\Admin;
use App\Models\Saas\Company\Company;
use App\Permissions\Saas\Companies\CompanyStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class CompanyChangeStatusTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;

    public function test_it_changes_company_active_status(): void
    {
        $admin = $this->loginAsSaasSuperAdmin();

        $admin->assignRole($this->createRole('CompanyStatus', [CompanyStatus::KEY], Admin::GUARD));

        $company = Company::factory()->create(['active' => true]);

        $newCompanyStatus = $this->putJson(
            route(
                'v1.saas.companies.status',
                $company->id
            )
        )
            ->json('data.active');

        self::assertFalse($newCompanyStatus);

        $newCompanyStatus = $this->putJson(
            route(
                'v1.saas.companies.status',
                $company->id
            )
        )
            ->json('data.active');

        self::assertTrue($newCompanyStatus);
    }
}
