<?php

namespace Tests\Feature\Saas\Admins;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_show_profile_for_not_auth_admin(): void
    {
        $this->getJson(route('v1.saas.profile.profile'))
            ->assertUnauthorized();
    }

    public function test_auth_admin_cant_show_self_profile(): void
    {
        $admin = $this->loginAsSaasAdmin();

        $response = $this->getJson(route('v1.saas.profile.profile'))
            ->assertOk();

        $profile = $response->json('data');

        self::assertEquals($admin->id, $profile['id']);
        self::assertEquals($admin->email, $profile['email']);
        self::assertEquals($admin->phone, $profile['phone']);
        self::assertEquals($admin->full_name, $profile['full_name']);
    }
}
