<?php

namespace Tests\Feature\Saas\Admins;

class AdminListOrderTest extends BaseAdminManagerTest
{
    public function test_order_by_name(): void
    {
        $admin = $this->createAdmin(['full_name' => 'Delta'])->assignRole(
            $this->createRoleAdminManager()
        );

        $this->createAdmin(['full_name' => 'Alpha']);
        $this->createAdmin(['full_name' => 'Beta']);
        $this->createAdmin(['full_name' => 'Gamma']);

        $this->loginAsSaasAdmin($admin);

        $args = [
            'order' => 'full_name',
            'order_type' => 'asc',
        ];
        $users = $this->requestToAdminListRoute($args)
            ->assertOk()
            ->json('data');

        $users = collect($users);

        $user = $users->shift();
        self::assertEquals('Alpha', $user['full_name']);

        $user = $users->shift();
        self::assertEquals('Beta', $user['full_name']);

        $user = $users->shift();
        self::assertEquals('Delta', $user['full_name']);

        $user = $users->shift();
        self::assertEquals('Gamma', $user['full_name']);
    }

    public function test_order_by_name_desc(): void
    {
        $admin = $this->createAdmin(['full_name' => 'Delta'])->assignRole(
            $this->createRoleAdminManager()
        );

        $this->createAdmin(['full_name' => 'Alpha']);
        $this->createAdmin(['full_name' => 'Beta']);
        $this->createAdmin(['full_name' => 'Gamma']);

        $this->loginAsSaasAdmin($admin);

        $args = [
            'order' => 'full_name',
            'order_type' => 'desc',
        ];
        $users = $this->requestToAdminListRoute($args)
            ->assertOk()
            ->json('data');

        $users = collect($users);
        $user = $users->shift();
        self::assertEquals('saas super admin', $user['full_name']);

        $user = $users->shift();
        self::assertEquals('Gamma', $user['full_name']);

        $user = $users->shift();
        self::assertEquals('Delta', $user['full_name']);

        $user = $users->shift();
        self::assertEquals('Beta', $user['full_name']);

        $user = $users->shift();
        self::assertEquals('Alpha', $user['full_name']);




    }
}
