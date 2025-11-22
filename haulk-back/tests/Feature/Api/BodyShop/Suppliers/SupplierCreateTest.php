<?php

namespace Api\BodyShop\Suppliers;

use App\Models\BodyShop\Suppliers\Supplier;
use App\Models\BodyShop\Suppliers\SupplierContact;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class SupplierCreateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $this->postJson(route('body-shop.suppliers.store'), [])->assertUnauthorized();
    }

    public function test_it_create_supplier()
    {
        $this->loginAsBodyShopSuperAdmin();

        $supplier = [
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

        $formRequest = $supplier;
        $formRequest['contacts'] = [$contact];

        $this->assertDatabaseMissing(Supplier::TABLE_NAME, $supplier);
        $this->assertDatabaseMissing(SupplierContact::TABLE_NAME, $contact);

        $this->postJson(route('body-shop.suppliers.store'), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(Supplier::TABLE_NAME, $supplier);
        $this->assertDatabaseHas(SupplierContact::TABLE_NAME, $contact);
    }

    /**
     * @param $attributes
     * @param $expectErrors
     * @dataProvider formSubmitDataProvider
     */
    public function test_it_see_validation_messages($attributes, $expectErrors)
    {
        $this->loginAsBodyShopAdmin();

        $this->postJson(route('body-shop.suppliers.store'), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => $expectErrors,
                ]
            );
    }

    public function formSubmitDataProvider(): array
    {
        $supplierName = 'Name Test';
        $contactName = 'Contact name';
        $phone = '1-541-754-3010';
        $email = 'some.email@example.com';

        return [
            [
                [
                    'name' => $supplierName,
                    'contacts' => [],
                ],
                [
                    [
                        'source' => ['parameter' => 'contacts'],
                        'title' => 'The Contacts field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'name' => $supplierName,
                    'contacts' => [
                        [
                            'is_main' => true,
                            'name' => $contactName,
                            'phone' => $phone,
                            'email' => null,
                        ],
                    ],
                ],
                [
                    [
                        'source' => ['parameter' => 'contacts.0.email'],
                        'title' => 'The Email field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'name' => $supplierName,
                    'contacts' => [
                        [
                            'is_main' => true,
                            'name' => $contactName,
                            'phone' => null,
                            'email' => $email,
                        ],
                    ],
                ],
                [
                    [
                        'source' => ['parameter' => 'contacts.0.phone'],
                        'title' => 'The Phone field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
        ];
    }

    public function test_it_create_with_not_unique_data(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $phone = '1-541-754-3010';
        $email = 'test@test.com';

        $supplier = factory(Supplier::class)->create();
        $supplierContact = factory(SupplierContact::class)->create([
            'is_main' => true,
            'phone' => $phone,
            'email' => $email,
            'supplier_id' => $supplier->id,
        ]);

        $formRequest = [
            'name' => 'Name Test',
            'url' => 'https://test.com',
            'contacts' => [
                [
                    'is_main' => true,
                    'name' => 'Contact name',
                    'email' => 'some.email@example.com',
                    'phone' => '1-541-754-3220',
                    'position' => 'Dev',
                ]
            ],
        ];

        $this->postJson(route('body-shop.suppliers.store'), $formRequest)
            ->assertCreated();

        $formRequest['contacts'][0]['phone'] = $phone;

        $this->postJson(route('body-shop.suppliers.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['contacts'][0]['email'] = $email;

        $this->postJson(route('body-shop.suppliers.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['contacts'][0]['phone'] = '1-541-754-3220';

        $this->postJson(route('body-shop.suppliers.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
