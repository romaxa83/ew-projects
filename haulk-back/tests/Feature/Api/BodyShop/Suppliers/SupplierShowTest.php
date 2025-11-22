<?php

namespace Api\BodyShop\Suppliers;

use App\Models\BodyShop\Suppliers\Supplier;
use App\Models\BodyShop\Suppliers\SupplierContact;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SupplierShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_supplier_for_unauthorized_users()
    {
        $supplier = factory(Supplier::class)->create();

        $this->getJson(route('body-shop.suppliers.show', $supplier))->assertUnauthorized();
    }

    public function test_it_not_show_supplier_for_not_permitted_users()
    {
        $supplier = factory(Supplier::class)->create();

        $this->loginAsCarrierAdmin();

        $this->getJson(route('body-shop.suppliers.show', $supplier))
            ->assertForbidden();
    }

    public function test_it_show_supplier_for_permitted_users()
    {
        $supplier = factory(Supplier::class)->create();
        factory(SupplierContact::class)->create(['supplier_id' => $supplier->id, 'is_main' => true]);
        factory(SupplierContact::class)->create(['supplier_id' => $supplier->id]);

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.suppliers.show', $supplier))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'url',
                'hasRelatedEntities',
                'contacts' => [
                    '*' => [
                        'id',
                        'is_main',
                        'name',
                        'email',
                        'phone',
                        'phone_extension',
                        'emails',
                        'phones',
                        'position',
                    ]
                ],
            ]]);

        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.suppliers.show', $supplier))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'url',
                'hasRelatedEntities',
                'contacts' => [
                    '*' => [
                        'id',
                        'is_main',
                        'name',
                        'email',
                        'phone',
                        'phone_extension',
                        'emails',
                        'phones',
                        'position',
                    ]
                ],
            ]]);
    }
}
