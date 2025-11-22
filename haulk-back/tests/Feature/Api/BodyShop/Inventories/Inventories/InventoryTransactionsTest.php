<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Transaction;
use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class InventoryTransactionsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $inventory = factory(Inventory::class)->create();
        $this->postJson(route('body-shop.inventories.purchase', $inventory), [])->assertUnauthorized();
        $this->postJson(route('body-shop.inventories.sold', $inventory), [])->assertUnauthorized();
    }

    public function test_it_purchase(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $inventory = factory(Inventory::class)->create();

        $formRequest = [
            'quantity' => 10,
            'cost' => 14.3,
            'invoice_number' => 'JHG232JH',
            'date' => now()->format('m/d/Y'),
        ];

        $dbData = [
            'operation_type' => Transaction::OPERATION_TYPE_PURCHASE,
            'quantity' => 10,
            'price' => 14.3,
            'invoice_number' => 'JHG232JH',
            'transaction_date' => now()->format('m/d/Y'),
            'order_id' => null,
            'inventory_id' => $inventory->id,
        ];

        $this->assertDatabaseMissing(Transaction::TABLE_NAME, $dbData);

        $this->postJson(route('body-shop.inventories.purchase', $inventory), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(Transaction::TABLE_NAME, $dbData);
    }

    public function test_it_purchase_without_invoice_number(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $inventory = factory(Inventory::class)->create();

        $formRequest = [
            'quantity' => 10,
            'cost' => 14.3,
            'date' => now()->format('m/d/Y'),
        ];

        $dbData = [
            'operation_type' => Transaction::OPERATION_TYPE_PURCHASE,
            'quantity' => 10,
            'price' => 14.3,
            'invoice_number' => null,
            'transaction_date' => now()->format('m/d/Y'),
            'order_id' => null,
            'inventory_id' => $inventory->id,
        ];

        $this->assertDatabaseMissing(Transaction::TABLE_NAME, $dbData);

        $this->postJson(route('body-shop.inventories.purchase', $inventory), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(Transaction::TABLE_NAME, $dbData);
    }

    public function test_it_sold(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $inventory = factory(Inventory::class)->create(['quantity' => 20]);

        $formRequest = [
            'quantity' => 10,
            'price' => 14.3,
            'date' => now()->format('m/d/Y'),
            'describe' => Transaction::DESCRIBE_BROKE,
        ];

        $dbData = [
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
            'quantity' => 10,
            'price' => 14.3,
            'invoice_number' => null,
            'transaction_date' => now()->format('m/d/Y'),
            'order_id' => null,
            'inventory_id' => $inventory->id,
            'describe' => Transaction::DESCRIBE_BROKE,
        ];

        $this->assertDatabaseMissing(Transaction::TABLE_NAME, $dbData);

        $this->postJson(route('body-shop.inventories.sold', $inventory), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(Transaction::TABLE_NAME, $dbData);
    }

    public function test_it_sold_with_sold_describe(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $inventory = factory(Inventory::class)->create(['quantity' => 20]);

        $formRequest = [
            'quantity' => 10,
            'price' => 14.3,
            'date' => now()->format('m/d/Y'),
            'describe' => Transaction::DESCRIBE_SOLD,
            'invoice_number' => 'GFSDSD',
            'discount' => '10',
            'tax' => 20,
            'payment_date' => now()->format('m/d/Y'),
            'payment_method' => Transaction::PAYMENT_METHOD_CHECK,
            'company_name' => '',
            'first_name' => 'FName',
            'last_name' => 'LName',
            'phone' => '1-541-754-3010',
            'email' => 'test@test.com',
        ];

        $dbData = [
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
            'quantity' => 10,
            'price' => 14.3,
            'transaction_date' => now()->format('m/d/Y'),
            'order_id' => null,
            'inventory_id' => $inventory->id,
            'describe' => Transaction::DESCRIBE_SOLD,
            'invoice_number' => 'GFSDSD',
            'discount' => '10',
            'tax' => 20,
            'payment_date' => now()->format('m/d/Y'),
            'payment_method' => Transaction::PAYMENT_METHOD_CHECK,
            'first_name' => 'FName',
            'last_name' => 'LName',
            'phone' => '1-541-754-3010',
            'email' => 'test@test.com',
        ];

        $this->assertDatabaseMissing(Transaction::TABLE_NAME, $dbData);

        $this->postJson(route('body-shop.inventories.sold', $inventory), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(Transaction::TABLE_NAME, $dbData);
    }

    public function test_it_sold_with_sold_describe_and_only_company_name(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $inventory = factory(Inventory::class)->create(['quantity' => 20]);

        $formRequest = [
            'quantity' => 10,
            'price' => 14.3,
            'date' => now()->format('m/d/Y'),
            'describe' => Transaction::DESCRIBE_SOLD,
            'invoice_number' => 'GFSDSD',
            'discount' => '10',
            'tax' => 20,
            'payment_date' => now()->format('m/d/Y'),
            'payment_method' => Transaction::PAYMENT_METHOD_CHECK,
            'company_name' => 'Company name',
            'phone' => '1-541-754-3010',
            'email' => 'test@test.com',
            'first_name' => '',
            'last_name' => '',
        ];

        $dbData = [
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
            'quantity' => 10,
            'price' => 14.3,
            'transaction_date' => now()->format('m/d/Y'),
            'order_id' => null,
            'inventory_id' => $inventory->id,
            'describe' => Transaction::DESCRIBE_SOLD,
            'invoice_number' => 'GFSDSD',
            'discount' => '10',
            'tax' => 20,
            'payment_date' => now()->format('m/d/Y'),
            'payment_method' => Transaction::PAYMENT_METHOD_CHECK,
            'company_name' => 'Company name',
            'phone' => '1-541-754-3010',
            'email' => 'test@test.com',
        ];

        $this->assertDatabaseMissing(Transaction::TABLE_NAME, $dbData);

        $this->postJson(route('body-shop.inventories.sold', $inventory), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(Transaction::TABLE_NAME, $dbData);
    }

    public function test_it_sold_with_sold_describe_validation(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $inventory = factory(Inventory::class)->create(['quantity' => 20]);

        $formRequest = [
            'quantity' => 10,
            'price' => 14.3,
            'date' => now()->format('m/d/Y'),
            'describe' => Transaction::DESCRIBE_SOLD,
        ];

        $this->postJson(route('body-shop.inventories.sold', $inventory), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'errors' => [
                    [
                        'source' => ['parameter' => 'invoice_number'],
                        'title' => 'The Invoice No field is required when Describe is sold.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                    [
                        'source' => ['parameter' => 'payment_date'],
                        'title' => 'The Payment Date field is required when Describe is sold.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                    [
                        'source' => ['parameter' => 'payment_method'],
                        'title' => 'The Payment Method field is required when Describe is sold.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                    [
                        'source' => ['parameter' => 'first_name'],
                        'title' => 'The First Name field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                    [
                        'source' => ['parameter' => 'last_name'],
                        'title' => 'The Last Name field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                    [
                        'source' => ['parameter' => 'company_name'],
                        'title' => 'The Company Name field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                    [
                        'source' => ['parameter' => 'phone'],
                        'title' => 'The Phone field is required when Describe is sold.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                    [
                        'source' => ['parameter' => 'email'],
                        'title' => 'The Email field is required when Describe is sold.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ],
            ]);
    }

    public function test_it_sold_with_quantity_more_then_in_stock(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $inventory = factory(Inventory::class)->create(['quantity' => 20]);

        $formRequest = [
            'quantity' => 30,
            'price' => 14.3,
            'date' => now()->format('m/d/Y'),
            'describe' => Transaction::DESCRIBE_BROKE,
        ];

        $dbData = [
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
            'quantity' => 30,
            'price' => 14.3,
            'invoice_number' => null,
            'transaction_date' => now()->format('m/d/Y'),
            'order_id' => null,
            'inventory_id' => $inventory->id,
            'describe' => Transaction::DESCRIBE_BROKE,
        ];

        $this->assertDatabaseMissing(Transaction::TABLE_NAME, $dbData);

        $this->postJson(route('body-shop.inventories.sold', $inventory), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
