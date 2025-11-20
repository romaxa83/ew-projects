<?php

namespace WezomCms\Core\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use WezomCms\Core\Models\Administrator;

class RedirectToAdminDashboardTest extends TestCase
{
    use DatabaseMigrations;

    public function testRedirectToAdminDashboardPage()
    {
        $administrator = factory(Administrator::class)
            ->states('active', 'super_admin')
            ->create([
                'email' => 'admin@admin.com',
                'password' => bcrypt('123123'),
            ]);

        redirect()->setIntendedUrl('/foo-bar?foo=bar');

        // Try login
        $response = $this->post(route('admin.login'), [
            'email' => $administrator->email,
            'password' => '123123',
        ]);

        // It must redirect to dashboard page
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function testRedirectToIntendedAdminPage()
    {
        $administrator = factory(Administrator::class)
            ->states('active', 'super_admin')
            ->create([
                'email' => 'admin@admin.com',
                'password' => bcrypt('123123'),
            ]);

        // Visit edit profile page for store intended url
        $this->get(route('admin.edit-profile'))->assertRedirect(route('admin.login-form'));


        // Try login
        $response = $this->post(route('admin.login'), [
            'email' => $administrator->email,
            'password' => '123123',
        ]);

        // It must redirect to edit profile page
        $response->assertRedirect(route('admin.edit-profile'));
    }
}
