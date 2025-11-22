<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Transaction;
use App\Models\BodyShop\Inventories\Category;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Unit;
use App\Models\BodyShop\Suppliers\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class InventoryCreateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users(): void
    {
        $this->postJson(route('body-shop.inventories.store'), [])->assertUnauthorized();
    }

    public function test_it_create(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'name' => 'Name Test',
            'stock_number' => 'JHGJHg3434',
            'price_retail' => 30.20,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create())->id,
        ];

        $purchase = [
            'quantity' => 10,
            'cost' => 20.25,
            'invoice_number' => 'SDSASD23324',
            'date' => now()->format('m/d/Y'),
        ];

        $this->assertDatabaseMissing(Inventory::TABLE_NAME, $formRequest);

        $response = $this->postJson(route('body-shop.inventories.store'), $formRequest + ['purchase' => $purchase])
            ->assertCreated();

        $createdId = $response['data']['id'];

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $formRequest);
        $this->assertDatabaseHas(
            Transaction::TABLE_NAME,
            [
                'quantity' => 10,
                'price' => 20.25,
                'invoice_number' => 'SDSASD23324',
                'transaction_date' => now()->format('m/d/Y'),
                'order_id' => null,
                'inventory_id' => $createdId,
            ]
        );
    }

    /**
     * @param $attributes
     * @param $expectErrors
     * @dataProvider formSubmitDataProvider
     */
    public function test_it_validation_messages($attributes, $expectErrors): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->postJson(route('body-shop.inventories.store'), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => $expectErrors,
                ]
            );
    }

    public function formSubmitDataProvider(): array
    {
        $this->refreshApplication();
        $name = 'Name Test';
        $stockNumber = 'JHGJHg3434';
        $quantity = 10;
        $priceWholesale = 10.00;
        $unitId = (factory(Unit::class)->create())->id;

        return [
            [
                [
                    'name' => null,
                    'stock_number' => $stockNumber,
                    'price_wholesale' => $priceWholesale,
                    'unit_id' => $unitId,
                    'purchase' => [
                        'quantity' => 10,
                        'cost' => 20.25,
                        'invoice_number' => 'SDSASD23324',
                        'date' => now()->format('m/d/Y'),
                    ],
                ],
                [
                    [
                        'source' => ['parameter' => 'name'],
                        'title' => 'The Name field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'name' => $name,
                    'stock_number' => null,
                    'price_wholesale' => $priceWholesale,
                    'unit_id' => $unitId,
                    'purchase' => [
                        'quantity' => 10,
                        'cost' => 20.25,
                        'invoice_number' => 'SDSASD23324',
                        'date' => now()->format('m/d/Y'),
                    ],
                ],
                [
                    [
                        'source' => ['parameter' => 'stock_number'],
                        'title' => 'The Stock Number field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'name' => $name,
                    'stock_number' => $stockNumber,
                    'price_wholesale' => $priceWholesale,
                    'unit_id' => $unitId,
                    'purchase' => [
                        'quantity' => null,
                        'cost' => 20.25,
                        'invoice_number' => 'SDSASD23324',
                        'date' => now()->format('m/d/Y'),
                    ],
                ],
                [
                    [
                        'source' => ['parameter' => 'purchase.quantity'],
                        'title' => 'The Quantity field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'name' => $name,
                    'stock_number' => $stockNumber,
                    'quantity' => $quantity,
                    'unit_id' => $unitId,
                    'purchase' => [
                        'quantity' => 10,
                        'cost' => null,
                        'invoice_number' => 'SDSASD23324',
                        'date' => now()->format('m/d/Y'),
                    ],
                ],
                [
                    [
                        'source' => ['parameter' => 'purchase.cost'],
                        'title' => 'The Cost field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
        ];
    }

    public function test_validation_of_quantity(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'name' => 'Name Test',
            'stock_number' => 'JHGJHg3434',
            'price_retail' => 30.20,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create())->id,
        ];

        $purchase = [
            'quantity' => 10.20,
            'cost' => 20.25,
            'invoice_number' => 'SDSASD23324',
            'date' => now()->format('m/d/Y'),
        ];

        $this->assertDatabaseMissing(Inventory::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.inventories.store'), $formRequest + ['purchase' => $purchase])
            ->assertCreated();

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $formRequest);

        $formRequest = [
            'name' => 'Name Test',
            'stock_number' => 'JHGJHg3434',
            'quantity' => 10.20,
            'price_retail' => 30.20,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create(['accept_decimals' => false]))->id,
        ];

        $this->assertDatabaseMissing(Inventory::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.inventories.store'), $formRequest + ['purchase' => $purchase])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $purchase['quantity'] = 10;
        $formRequest['stock_number'] = 'HDG34HF';
        $this->postJson(route('body-shop.inventories.store'), $formRequest + ['purchase' => $purchase])
            ->assertCreated();
    }

    public function test_it_create_with_min_limit(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'name' => 'Name Test',
            'stock_number' => 'JHGJHg3434',
            'price_retail' => 30.20,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create())->id,
            'min_limit' => 2,
        ];

        $purchase = [
            'quantity' => 10,
            'cost' => 20.25,
            'invoice_number' => 'SDSASD23324',
            'date' => now()->format('m/d/Y'),
        ];

        $this->assertDatabaseMissing(Inventory::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.inventories.store'), $formRequest + ['purchase' => $purchase])
            ->assertCreated();

        $this->assertDatabaseHas(Inventory::TABLE_NAME, $formRequest);
    }

    public function test_unique_validation(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $stockNumber = 'HGHGHG';
        factory(Inventory::class)->create(['stock_number' => $stockNumber]);

        $formRequest = [
            'name' => 'Name Test',
            'stock_number' => $stockNumber,
            'price_retail' => 30.20,
            'category_id' => (factory(Category::class)->create())->id,
            'supplier_id' => (factory(Supplier::class)->create())->id,
            'notes' => 'test notes',
            'unit_id' => (factory(Unit::class)->create())->id,
            'min_limit' => 2,
            'purchase' => [
                'quantity' => 10,
                'cost' => 20.25,
                'invoice_number' => 'SDSASD23324',
                'date' => now()->format('m/d/Y'),
            ],
        ];

        $this->postJson(route('body-shop.inventories.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
