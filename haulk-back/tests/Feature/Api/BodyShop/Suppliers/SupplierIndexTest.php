<?php

namespace Api\BodyShop\Suppliers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SupplierIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('body-shop.suppliers.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierDispatcher();

        $this->getJson(route('body-shop.suppliers.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_suppliers_for_bs_super_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.suppliers.index'))
            ->assertOk();
    }

    public function test_it_show_all_suppliers_for_body_shop_admin(): void
    {
        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.suppliers.index'))
            ->assertOk();
    }
}
