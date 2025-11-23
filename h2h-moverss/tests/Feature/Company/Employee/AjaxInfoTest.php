<?php

namespace Tests\Feature\Company\Employee;

use App\Enums\Employee\SalesTeamEnum;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class AjaxInfoTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected EmployeeBuilder $employeeBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function success_check_sales_team_type()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $this->post(route('company.employees.record', ['id' => $model->id]))
            ->assertJson([
                'success' => true,
                'record' => [
                    'id' => $model->id,
                ],
                'types' => [
                    'sales_team' => SalesTeamEnum::forSelect('key')
                ],
            ])
        ;
    }
}
