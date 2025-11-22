<?php

namespace Tests\Feature\Api\Users\Profile;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class ProfileShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_profile_for_unauthorized_user()
    {
        $this->getJson(route('profile.show'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_profile_for_auth_user()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('profile.show'))
            ->assertOk()
            ->assertJsonStructure(
                ['data' => ['id', 'full_name', 'email', 'phone', 'role_id', 'photo', 'permissions', 'language']]
            );
    }
}
