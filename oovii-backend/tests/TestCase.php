<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Config;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Models\Role;
use WezomCms\Users\Models\User;
use WezomCms\Users\Repositories\Auth\PassportClientRepository;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function passportInit(): void
    {
        $this->artisan("passport:client --password --provider=users --name='Users'");

        $userPassportClient = $this->getPassportRepository()->findForUser();

        Config::set('cms.users.users.oauth_client.users.id', $userPassportClient->id);
        Config::set('cms.users.users.oauth_client.users.secret', $userPassportClient->secret);
    }

    protected function getPassportRepository(): PassportClientRepository
    {
        return resolve(PassportClientRepository::class);
    }

    protected function loginAsUser(User $user = null): User
    {
        if (!$user) {
            $user = User::factory()->create();
        }
        $this->actingAs($user, 'api');

        return $user;
    }

    protected function loginAsAdmin(Administrator $admin = null, array $permissions = [], string $roleName = null): Administrator
    {
        if (!$admin) {
            $admin = Administrator::factory()->create(['active' => true]);
        }

        if (count($permissions)) {
            $roleName = $roleName ?? 'new-role';
            /** @var Role $role */
            $role = Role::factory()->create([ 'name' => $roleName, 'permissions' => $permissions ]);
            $admin->roles()->attach($role->id);
        }

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    protected function loginAsProvider(Administrator $admin = null, array $permissions = []): Administrator
    {
        return $this->loginAsAdmin($admin, $permissions, Role::DEFAULT_PROVIDER);
    }

    public function headers()
    {
        return [
            'Accept' => 'application/json'
        ];
    }

}
