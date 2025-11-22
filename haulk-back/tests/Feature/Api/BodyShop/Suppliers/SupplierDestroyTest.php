<?php

namespace Api\BodyShop\Suppliers;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Suppliers\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class SupplierDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_supplier_for_unauthorized_users()
    {
        $supplier = factory(Supplier::class)->create();

        $this->deleteJson(route('body-shop.suppliers.destroy', $supplier))->assertUnauthorized();
    }

    public function test_it_not_delete_supplier_for_not_permitted_users()
    {
        $supplier = factory(Supplier::class)->create();

        $this->loginAsCarrierSuperAdmin();

        $this->deleteJson(route('body-shop.suppliers.destroy', $supplier))
            ->assertForbidden();
    }

    public function test_it_delete_supplier_by_bs_super_admin()
    {
        $supplier = factory(Supplier::class)->create();

        $this->assertDatabaseHas(Supplier::TABLE_NAME, $supplier->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.suppliers.destroy', $supplier))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Supplier::TABLE_NAME, $supplier->getAttributes());
    }

    public function test_it_delete_supplier_by_bs_admin()
    {
        $supplier = factory(Supplier::class)->create();

        $this->assertDatabaseHas(Supplier::TABLE_NAME, $supplier->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.suppliers.destroy', $supplier))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Supplier::TABLE_NAME, $supplier->getAttributes());
    }

    public function test_cant_delete_supplier_with_relation()
    {
        $supplier = factory(Supplier::class)->create();
        factory(Inventory::class)->create(['supplier_id' => $supplier->id]);

        $this->assertDatabaseHas(Supplier::TABLE_NAME, $supplier->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.suppliers.destroy', $supplier))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(Supplier::TABLE_NAME, $supplier->getAttributes());
    }
}
