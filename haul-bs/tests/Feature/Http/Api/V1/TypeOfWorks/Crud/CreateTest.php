<?php

namespace Tests\Feature\Http\Api\V1\TypeOfWorks\Crud;

use App\Models\Inventories\Inventory;
use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\UnitBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected UnitBuilder $unitBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->unitBuilder = resolve(UnitBuilder::class);

        $this->data = [
            'name' => 'name',
            'duration' => '1:30',
            'hourly_rate' => 22.8,
        ];
    }

    /** @test */
    public function success_create()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.type-of-works.store'), $data)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'estimated_amount',
                ]
            ])
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'duration' => data_get($data, 'duration'),
                    'hourly_rate' => data_get($data, 'hourly_rate'),
                    'inventories' => [],
                ],
            ])
            ->assertJsonCount(0, 'data.inventories')
        ;
    }

    /** @test */
    public function success_create_with_inventories()
    {
        $this->loginUserAsSuperAdmin();

        $unit = $this->unitBuilder->accept_decimals(true)->create();

        /** @var $i_1 Inventory */
        $i_1 = $this->inventoryBuilder->create();
        $i_2 = $this->inventoryBuilder->unit($unit)->create();

        $data = $this->data;
        $data['inventories'][] = [
            'id' => $i_1->id,
            'quantity' => 10,
        ];
        $data['inventories'][] = [
            'id' => $i_2->id,
            'quantity' => 10.4,
        ];

        $this->postJson(route('api.v1.type-of-works.store'), $data)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'duration',
                    'hourly_rate',
                    'inventories' => [
                        [
                            'id',
                            'inventory_id',
                            'name',
                            'stock_number',
                            'price',
                            'quantity',
                            'unit' => [
                                'id',
                                'name',
                                'accept_decimals',
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'inventories' => [
                        [
                            'inventory_id' => $i_1->id,
                            'name' => $i_1->name,
                            'stock_number' => $i_1->stock_number,
                            'price' => $i_1->price_retail,
                            'quantity' => $data['inventories'][0]['quantity'],
                            'unit' => [
                                'id' => $i_1->unit->id
                            ],
                        ]
                    ]
                ],
            ])
            ->assertJsonCount(2, 'data.inventories')
        ;
    }
    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.type-of-works.store'), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly($res,
            __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'name');
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->postJson(route('api.v1.type-of-works.store'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertDatabaseEmpty(TypeOfWork::TABLE);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.type-of-works.store'), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['name', null, 'validation.required', ['attribute' => 'validation.attributes.name']],
            ['duration', null, 'validation.required', ['attribute' => 'validation.attributes.duration']],
            ['duration', '33', 'validation.regex', ['attribute' => 'validation.attributes.duration']],
            ['hourly_rate', null, 'validation.required', ['attribute' => 'validation.attributes.hourly_rate']],
            ['hourly_rate', 'aa', 'validation.numeric', ['attribute' => 'validation.attributes.hourly_rate']],
        ];
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.type-of-works.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.type-of-works.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
