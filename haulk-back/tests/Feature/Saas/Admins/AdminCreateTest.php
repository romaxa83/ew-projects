<?php

namespace Tests\Feature\Saas\Admins;

use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class AdminCreateTest extends TestCase
{
    use AdminFactory;
    use DatabaseTransactions;
    use PermissionFactory;

    public const ADMIN_EMAIL = 'new_admin@email.com';

    public function test_not_auth_admin_cant_create_other_admin(): void
    {
        $this->postJson(route('v1.saas.admins.store'))
            ->assertUnauthorized();
    }

    public function test_not_permitted_admin_cant_create_other_admin(): void
    {
        $this->loginAsSaasAdmin();

        $attrs = $this->newAdminAttributes();

        $this->postJson(route('v1.saas.admins.store'), $attrs)
            ->assertForbidden();
    }

    private function newAdminAttributes(): array
    {
        return [
            'full_name' => 'Admin name',
            'phone' => '+1123456789',
            'email' => self::ADMIN_EMAIL,
            'password' => 'newPassword123',
        ];
    }

    public function test_permitted_admin_can_create_other_admin(): void
    {
        $admin = $this->createAdmin()->assignRole(
            $this->createRoleAdminCreator()
        );

        $this->loginAsSaasAdmin($admin);

        $attrs = $this->newAdminAttributes();

        $this->assertDatabaseMissing(Admin::TABLE, ['email' => self::ADMIN_EMAIL]);

        $this->postJson(route('v1.saas.admins.store'), $attrs)
            ->assertCreated();

        $this->assertDatabaseHas(Admin::TABLE, ['email' => self::ADMIN_EMAIL]);
    }

}
