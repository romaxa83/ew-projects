<?php

namespace Api\BodyShop\Suppliers;

use App\Models\BodyShop\Suppliers\Supplier;
use App\Models\BodyShop\Suppliers\SupplierContact;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class SupplierUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_update_supplier_for_unauthorized_users()
    {
        $supplier = factory(Supplier::class)->create();

        $this->putJson(route('body-shop.suppliers.update', $supplier))->assertUnauthorized();
    }

    public function test_it_update_supplier_by_bs_super_admin()
    {
        $supplier = factory(Supplier::class)->create();

        $supplierData = [
            'name' => 'Name Test',
            'url' => 'https://test.com',
        ];

        $contact = [
            'is_main' => true,
            'name' => 'Contact name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            'position' => 'Dev',
        ];

        $formRequest = $supplierData;
        $formRequest['contacts'] = [$contact];

        $this->assertDatabaseMissing(Supplier::TABLE_NAME, $supplierData);
        $this->assertDatabaseMissing(SupplierContact::TABLE_NAME, $contact);

        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.suppliers.update', $supplier), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(Supplier::TABLE_NAME, $supplierData);
        $this->assertDatabaseHas(SupplierContact::TABLE_NAME, $contact);
    }

    public function test_it_update_with_not_unique_data(): void
    {
        $phone = '1-541-754-3010';
        $email = 'test@test.com';

        $existedSupplier = factory(Supplier::class)->create();
        factory(SupplierContact::class)->create([
            'is_main' => true,
            'phone' => $phone,
            'email' => $email,
            'supplier_id' => $existedSupplier->id,
        ]);

        $supplier = factory(Supplier::class)->create();
        $supplierContact = factory(SupplierContact::class)->create([
            'is_main' => true,
            'supplier_id' => $supplier->id,
            'phone' => '1-541-111-3010',
            'email' => 'test123@test.com',
        ]);

        $formRequest = [
            'name' => 'Name Test',
            'url' => 'https://test.com',
            'contacts' => [
                [
                    'is_main' => true,
                    'name' => 'Contact name',
                    'email' => $supplierContact->email,
                    'phone' => $supplierContact->phone,
                    'position' => 'Dev',
                ],
            ],
        ];

        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.suppliers.update', $supplier), $formRequest)
            ->assertOk();

        $formRequest['contacts'][0]['phone'] = $phone;
        $this->putJson(route('body-shop.suppliers.update', $supplier), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['contacts'][0]['email'] = $email;
        $this->putJson(route('body-shop.suppliers.update', $supplier), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['contacts'][0]['phone'] = $supplierContact->phone;
        $this->putJson(route('body-shop.suppliers.update', $supplier), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
