<?php

namespace Tests\Unit\Models\User;

use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Builder\UserBuilder;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function check_active(): void
    {
        /** @var $model User */
        $model = $this->userBuilder->setStatus(true)->create();

        $this->assertTrue($model->isActive());
    }

    /** @test */
    public function check_is_admin(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        /** @var $model User */
        $model = $this->userBuilder->setRole($role)->create();

        $this->assertTrue($model->isAdmin());
    }

    /** @test */
    public function check_is_ps(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $model User */
        $model = $this->userBuilder->setRole($role)->create();

        $this->assertTrue($model->isPS());
    }

    /** @test */
    public function check_is_pss(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PSS)->first();
        /** @var $model User */
        $model = $this->userBuilder->setRole($role)->create();

        $this->assertTrue($model->isPSS());
    }

    /** @test */
    public function check_is_sm(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_SM)->first();
        /** @var $model User */
        $model = $this->userBuilder->setRole($role)->create();

        $this->assertTrue($model->isSM());
    }

    /** @test */
    public function check_is_tm(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_TM)->first();
        /** @var $model User */
        $model = $this->userBuilder->setRole($role)->create();

        $this->assertTrue($model->isTM());
    }

    /** @test */
    public function check_is_tmd(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_TMD)->first();
        /** @var $model User */
        $model = $this->userBuilder->setRole($role)->create();

        $this->assertTrue($model->isTMD());
    }

    /** @test */
    public function check_get_role_name(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_TMD)->first();
        /** @var $model User */
        $model = $this->userBuilder->setRole($role)->create();

        $this->assertEquals($model->getRoleName(), $role->current[0]->text);
    }

    /** @test */
    public function check_get_role(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_TMD)->first();
        /** @var $model User */
        $model = $this->userBuilder->setRole($role)->create();

        $this->assertEquals($model->getRole(), $role->role);
    }

    /** @test */
    public function get_role_fullname(): void
    {
        /** @var $model User */
        $model = $this->userBuilder->withProfile()->create();

        $this->assertEquals(
            $model->fullName(),
            ucfirst($model->profile->first_name) . ' ' . ucfirst($model->profile->last_name)
        );
    }

    /** @test */
    public function check_ps_scope(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $model User */
        $model = $this->userBuilder->setRole($role)->create();

        $m = User::query()->ps()->first();

        $this->assertEquals($model->id, $m->id);
    }

    /** @test */
    public function check_not_ps_scope(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PSS)->first();
        /** @var $model User */
        $model = $this->userBuilder->setRole($role)->create();

        $m = User::query()->ps()->first();

        $this->assertNull($m);
    }
}

