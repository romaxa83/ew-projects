<?php

namespace Tests\Feature\Saas\Companies;

use App\Models\Saas\Company\Company;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class CompanyAdminListTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected CompanyBuilder $companyBuilder;
    protected UserBuilder $userBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function get_list(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $this->userBuilder->company($model)->asSuperAdmin()->create();
        $this->userBuilder->company($model)->asAdmin()->create();
        $this->userBuilder->company($model)->asDriver()->create();
        $this->userBuilder->company($model)->asDispatcher()->create();
        $this->userBuilder->company($model)->asDriver()->create();

        $this->getJson(route('v1.saas.companies.admins', [$model->id]))
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function get_list_by_search(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $this->userBuilder->company($model)->email('wet@gmail.com')->asSuperAdmin()->create();
        $this->userBuilder->company($model)->email('test-wet@gmail.com')->asAdmin()->create();
        $this->userBuilder->company($model)->email('test@gmail.com')->asAdmin()->create();
        $this->userBuilder->company($model)->asDispatcher()->create();
        $this->userBuilder->company($model)->asDriver()->create();

        $this->getJson(route('v1.saas.companies.admins', [
            'id' => $model->id,
            'email_search' => 'wet'
        ]))
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function get_info_empty(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $model Company */
        $model = $this->companyBuilder
            ->create();

        $this->getJson(route('v1.saas.companies.admins', [$model->id]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsSaasAdmin();

        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $this->getJson(route('v1.saas.companies.admins', [$model->id]))
            ->assertForbidden();
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $this->getJson(route('v1.saas.companies.admins', [$model->id]))
            ->assertUnauthorized();
    }
}

