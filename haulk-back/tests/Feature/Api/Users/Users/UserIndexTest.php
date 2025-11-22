<?php

namespace Tests\Feature\Api\Users\Users;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('users.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        self::markTestSkipped();
        $this->loginAsCarrierAccountant();

        $this->getJson(route('users.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_users_for_super_admin(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('users.index'))
            ->assertOk();
    }
}
