<?php

namespace Api\BodyShop\Suppliers;

use App\Models\BodyShop\Suppliers\Supplier;
use App\Models\BodyShop\Suppliers\SupplierContact;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class SupplierShortlistTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search()
    {
        $this->loginAsBodyShopSuperAdmin();

        $supplier1 = factory(Supplier::class)->create([
            'name' => 'Name1',
        ]);
        factory(SupplierContact::class)->create([
            'supplier_id' => $supplier1->id,
            'name' => 'Contact1 Name',
            'phone' => '+12345678',
            'email' => 'test@test.com',
            'is_main' => true,
        ]);

        $supplier2 = factory(Supplier::class)->create([
            'name' => 'Name2',
        ]);
        factory(SupplierContact::class)->create([
            'supplier_id' => $supplier2->id,
            'name' => 'Contact2 Name',
            'phone' => '+12375678',
            'email' => 'test2@test.com',
            'is_main' => true,
        ]);

        $supplier3 = factory(Supplier::class)->create([
            'name' => 'Name3',
        ]);
        factory(SupplierContact::class)->create([
            'supplier_id' => $supplier3->id,
            'name' => 'Contact3 Name',
            'phone' => '+123156789',
            'email' => 'test3@test.com',
            'is_main' => true,
        ]);

        $filter = ['q' => 'Name'];
        $response = $this->getJson(route('body-shop.suppliers.shortlist', $filter))
            ->assertOk();

        $suppliers = $response->json('data');
        $this->assertCount(3, $suppliers);
    }

    public function test_search_by_id()
    {
        $this->loginAsBodyShopSuperAdmin();

        $supplier1 = factory(Supplier::class)->create();
        $supplier2 = factory(Supplier::class)->create();
        $supplier3 = factory(Supplier::class)->create();

        $filter = ['searchid' => $supplier2->id];
        $response = $this->getJson(route('body-shop.suppliers.shortlist', $filter))
            ->assertOk();

        $suppliers = $response->json('data');
        $this->assertCount(1, $suppliers);
    }
}
