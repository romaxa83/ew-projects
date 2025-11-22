<?php

namespace Api\BodyShop\Companies;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CompanyIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('body-shop.companies.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('body-shop.companies.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_for_bs_super_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.companies.index'))
            ->assertOk();
    }

    public function test_it_show_all_for_body_shop_admin(): void
    {
        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.companies.index'))
            ->assertOk();
    }
}
