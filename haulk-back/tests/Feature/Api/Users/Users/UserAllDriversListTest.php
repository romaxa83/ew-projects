<?php

namespace Tests\Feature\Api\Users\Users;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserAllDriversListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_show_all_users_for_super_admin(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('drivers-list'))
            ->assertOk();
    }
}
