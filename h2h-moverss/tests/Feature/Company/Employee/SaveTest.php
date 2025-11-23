<?php

namespace Tests\Feature\Company\Employee;

use App\Enums\Employee\SalesTeamEnum;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Users\RoleBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class SaveTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected RoleBuilder $roleBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->roleBuilder = resolve(RoleBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function success_add_sales_team()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role = $this->roleBuilder->create();
        $user = $this->userBuilder->roles($role)->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->user($user)->create();

        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'l_name' => $model->l_name,
            'address' => $model->address,
            'signature' => $model->signature,
            'division_ids' => [$division->id],
            'roles' => [$role->id],
            'send_welcome' => false,
            'busy_weeks_days' => ['miscs' => [1]],
            'sales_team' => SalesTeamEnum::Local(),
        ];

        $this->assertNull($model->sales_team);

        $this->post(route('company.employees.record.save', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Employee changed.",
                'record' => [
                    'id' => $model->id,
                    'sales_team' => $data['sales_team'],
                ],
            ])
        ;
    }

    /** @test */
    public function success_change_sales_team()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $role = $this->roleBuilder->create();
        $user = $this->userBuilder->roles($role)->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder
            ->sales_team(SalesTeamEnum::Local_long)
            ->user($user)
            ->create();

        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'l_name' => $model->l_name,
            'address' => $model->address,
            'signature' => $model->signature,
            'division_ids' => [$division->id],
            'roles' => [$role->id],
            'send_welcome' => false,
            'busy_weeks_days' => ['miscs' => [1]],
            'sales_team' => SalesTeamEnum::Local(),
        ];

        $this->assertNotEquals($model->sales_team, $data['sales_team']);

        $this->post(route('company.employees.record.save', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Employee changed.",
                'record' => [
                    'id' => $model->id,
                    'sales_team' => $data['sales_team'],
                ],
            ])
        ;
    }
}

