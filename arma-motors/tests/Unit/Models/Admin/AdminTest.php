<?php

namespace Tests\Unit\Models\Admin;

use App\Models\Admin\Admin;
use App\Models\Dealership\Department;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class AdminTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function admin_delete_exist_role()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->createRoleWithPerm(Permissions::ADMIN_EDIT)->create();

        $admin->refresh();
        $this->assertNotNull($admin->role);

        $admin->deleteExistRoles();

        $admin->refresh();
        $this->assertNull($admin->role);
        $this->assertFalse($admin->hasDepartment());
    }

    /** @test */
    public function department_body()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->setDepartmentType(Department::TYPE_BODY)
            ->create();

        $admin->refresh();
        $this->assertTrue($admin->hasDepartment());
        $this->assertTrue($admin->isBodyDepartment());
        $this->assertFalse($admin->isServiceDepartment());
    }

    /** @test */
    public function department_credit()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->setDepartmentType(Department::TYPE_CREDIT)
            ->create();

        $admin->refresh();
        $this->assertTrue($admin->hasDepartment());
        $this->assertTrue($admin->isCreditDepartment());
        $this->assertFalse($admin->isServiceDepartment());
    }

    /** @test */
    public function department_service()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->setDepartmentType(Department::TYPE_SERVICE)
            ->create();

        $admin->refresh();
        $this->assertTrue($admin->hasDepartment());
        $this->assertTrue($admin->isServiceDepartment());
        $this->assertFalse($admin->isBodyDepartment());
    }

    /** @test */
    public function department_sales()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->setDepartmentType(Department::TYPE_SALES)
            ->create();

        $admin->refresh();
        $this->assertTrue($admin->hasDepartment());
        $this->assertTrue($admin->isSalesDepartment());
        $this->assertFalse($admin->isBodyDepartment());
    }

    /** @test */
    public function admin_active()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->setStatus(Admin::STATUS_ACTIVE)
            ->create();

        $admin->refresh();
        $this->assertTrue($admin->isActive());
        $this->assertFalse($admin->isInActive());
    }

    /** @test */
    public function admin_inactive()
    {
        $builder = $this->adminBuilder();
        $admin = $builder
            ->setStatus(Admin::STATUS_INACTIVE)
            ->create();

        $admin->refresh();
        $this->assertFalse($admin->isActive());
        $this->assertTrue($admin->isInActive());
    }
}



