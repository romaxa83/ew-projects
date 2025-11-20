<?php

namespace Tests\Unit\Models\User;

use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Builder\UserBuilder;

class RoleTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function check_active(): void
    {
        /** @var $model Role */
        $model = new Role();

        $this->assertEquals($model::getRoles(), [
            Role::ROLE_ADMIN,
            Role::ROLE_SM,
            Role::ROLE_TM,
            Role::ROLE_PS,
            Role::ROLE_PSS,
            Role::ROLE_TMD
        ]);
    }

    /** @test */
    public function check_users(): void
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $model User */
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role_ps)->create();

        $this->assertCount(2, $role->users);
        $this->assertCount(1, $role_ps->users);
    }
}
