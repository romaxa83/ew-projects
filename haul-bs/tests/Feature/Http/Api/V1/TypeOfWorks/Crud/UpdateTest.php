<?php

namespace Tests\Feature\Http\Api\V1\TypeOfWorks\Crud;

use App\Models\Inventories\Inventory;
use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkInventoryBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected TypeOfWorkBuilder $typeOfWorkBuilder;
    protected TypeOfWorkInventoryBuilder $typeOfWorkInventoryBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->typeOfWorkBuilder = resolve(TypeOfWorkBuilder::class);
        $this->typeOfWorkInventoryBuilder = resolve(TypeOfWorkInventoryBuilder::class);

        $this->data = [
            'name' => 'name',
            'duration' => '1:30',
            'hourly_rate' => 22.8,
        ];
    }

    /** @test */
    public function success_update()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $i Inventory */
        $i = $this->inventoryBuilder->create();

        /** @var $model TypeOfWork */
        $model = $this->typeOfWorkBuilder->create();

        $data = $this->data;
        $data['inventories'][] = [
            'id' => $i->id,
            'quantity' => 12,
        ];

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->duration, data_get($data, 'duration'));
        $this->assertNotEquals($model->hourly_rate, data_get($data, 'hourly_rate'));

        $this->assertCount(0, $model->inventories);

        $this->putJson(route('api.v1.type-of-works.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'duration' => data_get($data, 'duration'),
                    'hourly_rate' => data_get($data, 'hourly_rate'),
                    'inventories' => [
                        [
                            'inventory_id' => $i->id,
                        ]
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.inventories')
        ;
    }

    /** @test */
    public function success_update_inventories()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $i Inventory */
        $i = $this->inventoryBuilder->create();
        $i_1 = $this->inventoryBuilder->create();

        /** @var $model TypeOfWork */
        $model = $this->typeOfWorkBuilder->create();

        $this->typeOfWorkInventoryBuilder->inventory($i)->work($model)->create();
        $this->typeOfWorkInventoryBuilder->inventory($i_1)->work($model)->create();

        $data = $this->data;
        $data['inventories'][] = [
            'id' => $i->id,
            'quantity' => 120,
        ];

        $this->assertCount(2, $model->inventories);

        $this->putJson(route('api.v1.type-of-works.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'inventories' => [
                        [
                            'inventory_id' => $i->id,
                            'quantity' => $data['inventories'][0]['quantity'],
                        ]
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.inventories')
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->putJson(route('api.v1.type-of-works.update', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.type_of_works.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model TypeOfWork */
        $model = $this->typeOfWorkBuilder->create();

        $data['name'] = null;

        $res = $this->putJson(route('api.v1.type-of-works.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'name'
        );
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model TypeOfWork */
        $model = $this->typeOfWorkBuilder->create();

        $data = $this->data;

        $this->putJson(route('api.v1.type-of-works.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model TypeOfWork */
        $model = $this->typeOfWorkBuilder->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->putJson(route('api.v1.type-of-works.update', ['id' => $model->id]), $data)
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

        /** @var $model TypeOfWork */
        $model = $this->typeOfWorkBuilder->create();

        $res = $this->putJson(route('api.v1.type-of-works.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model TypeOfWork */
        $model = $this->typeOfWorkBuilder->create();

        $res = $this->putJson(route('api.v1.type-of-works.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
