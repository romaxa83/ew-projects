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
        $administrator = Administrator::factory()
            ->active()
            ->superAdmin()
            ->create([
                'email' => 'admin@admin.com',
                'password' => bcrypt(12345678),
            ]);

        redirect()->setIntendedUrl('/foo-bar?foo=bar');

        // Try login
        $response = $this->post(route('admin.login'), [
            'email' => $administrator->email,
            'password' => 12345678,
        ]);

        // It must redirect to dashboard page
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function testRedirectToIntendedAdminPage()
    {
        $administrator = Administrator::factory()
            ->active()
            ->superAdmin()
            ->create([
                'email' => 'admin@admin.com',
                'password' => bcrypt(12345678),
            ]);

        // Visit edit profile page for store intended url
        $this->get(route('admin.edit-profile'))->assertRedirect(route('admin.login-form'));


        // Try login
        $response = $this->post(route('admin.login'), [
            'email' => $administrator->email,
            'password' => 12345678,
        ]);

        // It must redirect to edit profile page
        $response->assertRedirect(route('admin.edit-profile'));
    }
}
