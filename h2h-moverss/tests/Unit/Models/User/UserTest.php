<?php

namespace Tests\Unit\Models\User;

use App\Models\User\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Users\RoleBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected RoleBuilder $roleBuilder;

    public function setUp(): void
    {
        $this->userBuilder = resolve(UserBuilder::class);
        $this->roleBuilder = resolve(RoleBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_is_partner()
    {
        /** @var $role Role */
        $role = $this->roleBuilder->asPartner()->create();
        $role_2 = $this->roleBuilder->asPartner()->create();

        /** @var $model User */
        $model = $this->userBuilder
            ->roles($role, $role_2)
            ->create();

        $this->assertTrue($model->isPartner());
    }

    /** @test */
    public function check_is_not_partner()
    {
        /** @var $role Role */
        $role = $this->roleBuilder->create();

        /** @var $model User */
        $model = $this->userBuilder
            ->roles($role)
            ->create();

        $this->assertFalse($model->isPartner());
    }

    /** @test */
    public function not_role()
    {
        /** @var $model User */
        $model = $this->userBuilder
            ->create();

        $this->assertFalse($model->isPartner());
    }
}
