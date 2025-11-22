<?php

namespace Tests\Feature\Api\BodyShop\Suppliers;

use App\Models\BodyShop\Suppliers\Supplier;
use App\Models\BodyShop\Suppliers\SupplierContact;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class SupplierIndexFilterTest extends TestCase
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
        $response = $this->getJson(route('body-shop.suppliers.index', $filter))
            ->assertOk();

        $suppliers = $response->json('data');
        $this->assertCount(3, $suppliers);

        $filter = ['q' => 'Name1'];
        $response = $this->getJson(route('body-shop.suppliers.index', $filter))
            ->assertOk();

        $suppliers = $response->json('data');
        $this->assertCount(1, $suppliers);
        $this->assertEquals($supplier1->id, $suppliers[0]['id']);

        $filter = ['q' => '+123156789'];
        $response = $this->getJson(route('body-shop.suppliers.index', $filter))
            ->assertOk();

        $suppliers = $response->json('data');
        $this->assertCount(1, $suppliers);
        $this->assertEquals($supplier3->id, $suppliers[0]['id']);

        $filter = ['q' => 'test2'];
        $response = $this->getJson(route('body-shop.suppliers.index', $filter))
            ->assertOk();

        $suppliers = $response->json('data');
        $this->assertCount(1, $suppliers);
        $this->assertEquals($supplier2->id, $suppliers[0]['id']);
    }

    public function test_search_by_additional_phones_and_emails(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $supplier1 = factory(Supplier::class)->create([
            'name' => 'Name1',
        ]);
        factory(SupplierContact::class)->create([
            'supplier_id' => $supplier1->id,
            'name' => 'Contact1 Name',
            'phone' => '+12345678',
            'phones' => [
                [
                    'number' => '+777444333',
                ],
            ],
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
            'emails' => [
                ['value' => 'qqqq@test.com',],
            ],
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

        $filter = ['q' => '444333'];
        $response = $this->getJson(route('body-shop.suppliers.index', $filter))
            ->assertOk();

        $suppliers = $response->json('data');
        $this->assertCount(1, $suppliers);
        $this->assertEquals($supplier1->id, $suppliers[0]['id']);

        $filter = ['q' => 'qq@test.com'];
        $response = $this->getJson(route('body-shop.suppliers.index', $filter))
            ->assertOk();

        $suppliers = $response->json('data');
        $this->assertCount(1, $suppliers);
        $this->assertEquals($supplier2->id, $suppliers[0]['id']);
    }
}
