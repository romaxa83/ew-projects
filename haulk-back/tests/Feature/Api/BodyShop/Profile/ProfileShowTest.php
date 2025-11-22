<?php

namespace Tests\Feature\Api\BodyShop\Profile;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class ProfileShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_profile_for_unauthorized_user()
    {
        $this->getJson(route('body-shop.profile.show'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_profile_for_auth_user()
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.profile.show'))
            ->assertOk()
            ->assertJsonStructure(
                ['data' => ['id', 'full_name', 'email', 'phone', 'role_id', 'photo', 'permissions', 'language']]
            );
    }
}
