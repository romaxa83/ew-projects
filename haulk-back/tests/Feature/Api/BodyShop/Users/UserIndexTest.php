<?php

namespace Tests\Feature\Api\BodyShop\Users;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('body-shop.users.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierAccountant();

        $this->getJson(route('body-shop.users.index'))
            ->assertForbidden();

        $this->loginAsBodyShopMechanic();

        $this->getJson(route('body-shop.users.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_users_for_bs_super_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.users.index'))
            ->assertOk();
    }

    public function test_it_show_all_users_for_bs_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.users.index'))
            ->assertOk();
    }
}
