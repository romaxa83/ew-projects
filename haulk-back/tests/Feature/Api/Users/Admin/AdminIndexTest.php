<?php

namespace Tests\Feature\Api\Users\Admin;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class AdminIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_admin_list_for_unauthorized()
    {

        $this->getJson(route('users.index'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_not_show_admin_list_for_not_permitted()
    {
        $this->loginAsCarrierDispatcher();

        $this->getJson(route('users.index'))
            ->assertOk();
    }

    public function test_it_show_for_permitted_user()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('users.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta',]);
    }
}
