<?php

namespace Tests\Feature\Http\Api\OneC\Catalog\Products;

use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class SerialNumbersControllerTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public function test_destroy(): void
    {
        $this->loginAsModerator();

        $serialNumber = ProductSerialNumber::factory()->create();
        $this->assertDatabaseHas(ProductSerialNumber::TABLE, [
            'product_id' => $serialNumber->product_id,
            'serial_number' => $serialNumber->serial_number,
        ]);

        $this->postJson(route('1c.serialNumbers.delete'), [
            'product_guid' => $serialNumber->product->guid,
            'serial_numbers' => [$serialNumber->serial_number]
        ])
            ->assertOk();

        $this->assertDatabaseMissing(ProductSerialNumber::TABLE, [
            'product_id' => $serialNumber->product_id,
            'serial_number' => $serialNumber->serial_number,
        ]);
    }

    public function test_import(): void
    {
        self::markTestSkipped('temporary disable statistics while import');

        $this->loginAsModerator();

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()
                    ->state(['serial_number' => $serialA = 'serial-A']),
                'serialNumbers'
            )->create();

        $productWillUpdateSerial = Product::factory()
            ->has(
                ProductSerialNumber::factory()
                    ->state(['serial_number' => $serialB = 'serial-B']),
                'serialNumbers'
            )
            ->create();

        $this->assertDatabaseHas(
            ProductSerialNumber::TABLE,
            ['product_id' => $productWillUpdateSerial->id, 'serial_number' => $serialB]
        );

        $this->assertDatabaseMissing(
            ProductSerialNumber::TABLE,
            ['product_id' => $product->id, 'serial_number' => $serialB]
        );

        $this->postJson(
            route('1c.serialNumbers.import'),
            [
                'product_guid' => $product->guid,
                'serial_numbers' => [
                    $serialA,
                    $serialB,
                    'serial-C',
                ]
            ]
        )
            ->assertJson(
                [
                    'data' => [
                        'given_serial_numbers' => 3,
                        'import_statistics' => [
                            'total' => 3,
                            'new' => 1,
                            'exists' => 1,
                            'updated' => 1,
                        ],
                        'errors' => [],
                    ],
                ]
            );

        $this->assertDatabaseHas(
            ProductSerialNumber::TABLE,
            ['product_id' => $product->id, 'serial_number' => $serialB]
        );

        $this->assertDatabaseMissing(
            ProductSerialNumber::TABLE,
            ['product_id' => $productWillUpdateSerial->id, 'serial_number' => $serialB]
        );
    }

    /** @test */
    public function import_to_uppercase(): void
    {
        $this->loginAsModerator();

        $product = Product::factory()->create();

        $serials = [
            'serial-1',
            'serial-2',
            'serial-3',
        ];

        $this->postJson(route('1c.serialNumbers.import'),
            [
                'product_guid' => $product->guid,
                'serial_numbers' => $serials
            ]
        )
            ->assertJson(
                [
                    'data' => [
                        'given_serial_numbers' => 3,
                        'import_statistics' => [
                            'total' => 3,
                            'new' => 0,
                            'exists' => 0,
                            'updated' => 0,
                        ],
                        'errors' => [],
                    ],
                ]
            );
    }
}
